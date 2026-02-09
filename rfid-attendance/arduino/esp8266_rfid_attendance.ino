/*
 * RFID Attendance - ESP8266 + MFRC522
 *
 * Hardware:
 * - NodeMCU ESP8266
 * - MFRC522 RFID Reader
 * - LED Putih (D1), Hijau (D2), Merah (D0)
 * - Buzzer (D4)
 *
 * Wiring:
 * MFRC522 -> NodeMCU
 * - SDA (SS) -> D8
 * - SCK      -> D5
 * - MOSI     -> D7
 * - MISO     -> D6
 * - RST      -> D3
 * - 3.3V     -> 3.3V
 * - GND      -> GND
 */

#include <SPI.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

#define SERIAL_BAUD 115200
#define LOGF(fmt, ...) Serial.printf("[%lu] " fmt "\n", millis(), ##__VA_ARGS__)

// ======== WIFI & API CONFIG ========
// Copy `secrets.example.h` to `secrets.h` and fill credentials.
// `secrets.h` is ignored by git to prevent leaking secrets.
#if defined(__has_include)
#  if __has_include("secrets.h")
#    include "secrets.h"
#  endif
#endif

#ifndef WIFI_SSID
#define WIFI_SSID "YOUR_WIFI_SSID"
#endif

#ifndef WIFI_PASSWORD
#define WIFI_PASSWORD "YOUR_WIFI_PASSWORD"
#endif

// IMPORTANT: gunakan IP server, bukan localhost
// contoh: http://192.168.43.6:8000/api/rfid/scan
// Test mode (UID capture only): ganti ke /api/rfid/peek
// contoh: http://192.168.43.6:8000/api/rfid/peek
const char* API_URL = "http://192.168.43.6:8000/api/rfid/scan";
const char* PEEK_URL = "http://192.168.43.6:8000/api/rfid/peek";
// Health check (API + DB): /api/health
// contoh: http://192.168.43.6:8000/api/health
const char* HEALTH_URL = "http://192.168.43.6:8000/api/health";

#ifndef DEVICE_TOKEN
#define DEVICE_TOKEN "YOUR_DEVICE_TOKEN"
#endif

// ======== PIN MAPPING ========
constexpr uint8_t PIN_LED_WHITE = D1;
constexpr uint8_t PIN_LED_GREEN = D2;
constexpr uint8_t PIN_LED_RED   = D0;
constexpr uint8_t PIN_BUZZER    = D4;

constexpr uint8_t PIN_SS  = D8;
constexpr uint8_t PIN_RST = D3;

MFRC522 rfid(PIN_SS, PIN_RST);

// ======== SETTINGS ========
const unsigned long SCAN_COOLDOWN_MS = 2500;
const unsigned long WIFI_RECONNECT_INTERVAL_MS = 10000;
const unsigned long WIFI_CONNECT_TIMEOUT_MS = 8000;
const unsigned long HTTP_TIMEOUT_MS = 5000;
const unsigned long HEALTH_CHECK_INTERVAL_MS = 15000;
const unsigned long HEALTH_TIMEOUT_MS = 3000;
String lastUid = "";
unsigned long lastScanAt = 0;
unsigned long lastWiFiAttempt = 0;
unsigned long wifiAttemptStarted = 0;
bool wifiConnecting = false;
wl_status_t lastWiFiStatus = WL_IDLE_STATUS;
unsigned long lastHealthCheckAt = 0;
bool apiHealthy = false;
bool dbHealthy = false;
bool captureMode = false;

const char* wifiStatusToString(wl_status_t status) {
  switch (status) {
    case WL_IDLE_STATUS: return "IDLE";
    case WL_NO_SSID_AVAIL: return "NO_SSID";
    case WL_SCAN_COMPLETED: return "SCAN_COMPLETED";
    case WL_CONNECTED: return "CONNECTED";
    case WL_CONNECT_FAILED: return "CONNECT_FAILED";
    case WL_CONNECTION_LOST: return "CONNECTION_LOST";
    case WL_WRONG_PASSWORD: return "WRONG_PASSWORD";
    case WL_DISCONNECTED: return "DISCONNECTED";
    default: return "UNKNOWN";
  }
}

String maskToken(const char* token) {
  String t(token);
  int len = t.length();
  if (len <= 8) {
    return t;
  }
  return t.substring(0, 4) + "..." + t.substring(len - 4);
}

void beep(uint8_t times, uint16_t onMs = 80, uint16_t offMs = 80) {
  for (uint8_t i = 0; i < times; i++) {
    digitalWrite(PIN_BUZZER, HIGH);
    delay(onMs);
    digitalWrite(PIN_BUZZER, LOW);
    delay(offMs);
  }
}

void setLeds(bool whiteOn, bool greenOn, bool redOn) {
  digitalWrite(PIN_LED_WHITE, whiteOn ? HIGH : LOW);
  digitalWrite(PIN_LED_GREEN, greenOn ? HIGH : LOW);
  digitalWrite(PIN_LED_RED, redOn ? HIGH : LOW);
}

void ledIdle() {
  setLeds(true, false, false);
}

void blinkOverlay(uint8_t times, bool greenOn, bool redOn, uint16_t onMs, uint16_t offMs) {
  for (uint8_t i = 0; i < times; i++) {
    setLeds(true, greenOn, redOn);
    delay(onMs);
    ledIdle();
    delay(offMs);
  }
}

void startupSequence() {
  ledIdle();
  blinkOverlay(1, true, false, 120, 80);
  blinkOverlay(1, true, true, 120, 80);
  blinkOverlay(1, false, true, 120, 80);
  ledIdle();
}

void updateHealthStatus(bool apiOk, bool dbOk) {
  apiHealthy = apiOk;
  dbHealthy = dbOk;

  if (!apiOk) {
    blinkOverlay(1, false, true, 120, 80);
    return;
  }

  if (!dbOk) {
    blinkOverlay(2, true, true, 100, 80);
    return;
  }

  blinkOverlay(1, true, false, 100, 80);
}

void checkHealth(bool force = false, bool updateIndicators = true) {
  unsigned long nowMs = millis();
  if (!force && (nowMs - lastHealthCheckAt) < HEALTH_CHECK_INTERVAL_MS) {
    return;
  }
  lastHealthCheckAt = nowMs;

  if (!ensureWiFi()) {
    apiHealthy = false;
    dbHealthy = false;
    captureMode = false;
    if (updateIndicators) {
      updateHealthStatus(false, false);
    }
    LOGF("Health skipped: WiFi not connected");
    return;
  }

  HTTPClient http;
  WiFiClient client;
  client.setTimeout(HEALTH_TIMEOUT_MS / 1000);
  http.begin(client, HEALTH_URL);
  http.addHeader("X-Device-Token", DEVICE_TOKEN);
  http.setTimeout(HEALTH_TIMEOUT_MS);

  LOGF("Health GET %s", HEALTH_URL);
  int httpCode = http.GET();
  String resp = http.getString();
  http.end();

  bool ok = false;
  bool dbOk = false;
  bool newCaptureMode = captureMode;
  String modeStr = "scan";

  if (httpCode > 0) {
    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, resp);
    if (!err) {
      ok = doc["ok"] | false;
      dbOk = doc["db"] | false;
      const char* mode = doc["mode"] | "scan";
      modeStr = String(mode);
      newCaptureMode = modeStr == "peek";
    } else {
      LOGF("Health JSON parse error: %s", err.c_str());
    }
  } else {
    LOGF("Health HTTP error: %d", httpCode);
  }

  apiHealthy = ok;
  dbHealthy = dbOk;

  if (newCaptureMode != captureMode) {
    captureMode = newCaptureMode;
    LOGF("Device mode changed -> %s", captureMode ? "PEEK (listening)" : "SCAN (normal)");
  } else {
    captureMode = newCaptureMode;
  }

  LOGF("Health http=%d ok=%s db=%s mode=%s resp=%s", httpCode, ok ? "true" : "false", dbOk ? "true" : "false", modeStr.c_str(), resp.c_str());

  if (updateIndicators) {
    updateHealthStatus(ok, dbOk);
  }
}

String uidToString(MFRC522::Uid *uid) {
  String uidStr = "";
  for (byte i = 0; i < uid->size; i++) {
    if (uid->uidByte[i] < 0x10) uidStr += "0";
    uidStr += String(uid->uidByte[i], HEX);
  }
  uidStr.toUpperCase();
  return uidStr;
}

void startWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);
  WiFi.setAutoReconnect(true);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  wifiConnecting = true;
  wifiAttemptStarted = millis();
  lastWiFiAttempt = millis();
  LOGF("WiFi begin SSID=%s", WIFI_SSID);
}

bool ensureWiFi() {
  wl_status_t status = WiFi.status();

  if (status != lastWiFiStatus) {
    lastWiFiStatus = status;
    LOGF("WiFi status=%s", wifiStatusToString(status));
  }

  if (status == WL_CONNECTED) {
    if (wifiConnecting) {
      wifiConnecting = false;
      LOGF("WiFi connected ip=%s", WiFi.localIP().toString().c_str());
    }
    return true;
  }

  unsigned long nowMs = millis();

  if (wifiConnecting && (nowMs - wifiAttemptStarted) >= WIFI_CONNECT_TIMEOUT_MS) {
    LOGF("WiFi connect timeout, retrying...");
    WiFi.disconnect();
    wifiConnecting = false;
  }

  if (!wifiConnecting && (nowMs - lastWiFiAttempt) >= WIFI_RECONNECT_INTERVAL_MS) {
    startWiFi();
  }

  return false;
}

void setup() {
  Serial.begin(SERIAL_BAUD);
  delay(100);
  LOGF("Boot ESP8266 RFID Attendance");
  LOGF("Config ssid=%s api=%s peek=%s health=%s token=%s", WIFI_SSID, API_URL, PEEK_URL, HEALTH_URL, maskToken(DEVICE_TOKEN).c_str());

  pinMode(PIN_LED_WHITE, OUTPUT);
  pinMode(PIN_LED_GREEN, OUTPUT);
  pinMode(PIN_LED_RED, OUTPUT);
  pinMode(PIN_BUZZER, OUTPUT);
  ledIdle();

  SPI.begin();
  rfid.PCD_Init();
  rfid.PCD_SetAntennaGain(MFRC522::RxGain_max);
  LOGF("RFID reader ready.");

  startWiFi();
  startupSequence();
}

void handleScan(const String& uid) {
  if (!ensureWiFi()) {
    LOGF("Scan blocked: WiFi not connected uid=%s", uid.c_str());
    blinkOverlay(3, false, true, 120, 80);
    beep(3, 80, 60);
    return;
  }

  checkHealth(true, false);

  const char* targetUrl = captureMode ? PEEK_URL : API_URL;
  LOGF("Scan uid=%s mode=%s url=%s", uid.c_str(), captureMode ? "PEEK" : "SCAN", targetUrl);

  HTTPClient http;
  WiFiClient client;
  client.setTimeout(HTTP_TIMEOUT_MS / 1000);
  http.begin(client, targetUrl);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Token", DEVICE_TOKEN);
  http.setTimeout(HTTP_TIMEOUT_MS);

  StaticJsonDocument<128> payload;
  payload["uid"] = uid;
  String body;
  serializeJson(payload, body);
  LOGF("HTTP POST body=%s", body.c_str());

  int httpCode = http.POST(body);
  String resp = http.getString();
  http.end();

  LOGF("HTTP %d resp=%s", httpCode, resp.c_str());

  bool ok = false;
  String code = "";
  String message = "";
  if (httpCode > 0) {
    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, resp);
    if (!err) {
      ok = doc["ok"] | false;
      code = doc["code"] | "";
      message = doc["message"] | "";
    } else {
      LOGF("API JSON parse error: %s", err.c_str());
    }
  } else {
    LOGF("HTTP error: %d", httpCode);
  }

  if (httpCode == 401 || code == "UNAUTHORIZED") {
    LOGF("UNAUTHORIZED: check DEVICE_TOKEN. message=%s", message.c_str());
  }

  if (ok) {
    blinkOverlay(2, true, false, 120, 80);
    beep(1, 120, 60);
  } else if (code.indexOf("ALREADY") >= 0 || code.indexOf("OUTSIDE") >= 0) {
    blinkOverlay(2, true, true, 100, 80);
    beep(2, 60, 60);
  } else {
    blinkOverlay(3, false, true, 120, 80);
    beep(3, 80, 60);
  }

  delay(200);
  ledIdle();
}

void loop() {
  ensureWiFi();
  checkHealth();

  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }
  if (!rfid.PICC_ReadCardSerial()) {
    return;
  }

  String uid = uidToString(&rfid.uid);

  unsigned long nowMs = millis();
  if (uid == lastUid && (nowMs - lastScanAt) < SCAN_COOLDOWN_MS) {
    LOGF("Cooldown skip uid=%s", uid.c_str());
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastUid = uid;
  lastScanAt = nowMs;

  LOGF("Card detected uid=%s", uid.c_str());

  handleScan(uid);

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}
