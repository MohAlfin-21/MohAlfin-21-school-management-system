# Panduan Lengkap -- RFID Attendance (XI RPL 1)

Dokumen ini menjelaskan sistem absensi RFID yang ada di repo ini dari nol sampai bisa dipakai, dengan bahasa yang mudah. Di bagian akhir ada penjelasan teknis (untuk yang mau ngoprek).

---

## 1) Gambaran Besar (Apa yang sistem ini lakukan)

Sistem ini adalah absensi sekolah berbasis RFID:
- Siswa menempelkan kartu RFID ke alat (ESP8266 + MFRC522).
- Alat mengirim UID kartu ke server Laravel lewat internet (HTTP).
- Server mencatat absensi check-in dan check-out sesuai jam yang sudah ditentukan.
- Kalau siswa izin/sakit, siswa upload foto surat ke web -> sekretaris kelas yang menyetujui/menolak.
- Guru hanya melihat daftar hadir dan rekap (read-only).

Kenapa ada web + alat?
- Alat RFID hanya membaca kartu dan mengirim UID.
- Web yang mengurus aturan jam, siapa pemilik kartu, kelasnya, laporan, dan approval izin.

---

## 2) Role (Peran) dan Hak Akses

Sistem ini memakai 4 role:

### Admin
Admin adalah operator sistem:
- Buat/ubah user dan role.
- Buat kelas dan isi anggota kelas.
- Menentukan siapa sekretaris kelas (juga harus role secretary).
- Daftarkan kartu RFID ke siswa.
- Buat device token untuk alat RFID.
- Atur jam check-in/check-out dan batas upload.
- Test kartu RFID dan ambil UID terbaru tanpa mengubah absensi (fitur Live Capture).

### Teacher (Guru / Wali Kelas)
Untuk MVP ini, teacher dianggap wali kelas (bukan guru mapel).
- Bisa melihat kelas yang dia pegang sebagai wali kelas.
- Bisa membuka attendance harian.
- Bisa export daftar hadir ke file.
- Bisa membuka profile siswa di kelasnya (read-only).

### Secretary (Sekretaris kelas)
Ini adalah siswa yang punya tanggung jawab tambahan:
- Melihat dashboard kelas.
- Mengelola attendance harian (manual status dan catatan).
- Melakukan early checkout (pulang sebelum jam normal) dengan alasan.
- Approve / reject permohonan izin/sakit dari siswa di kelasnya.

### Student (Siswa)
- Melihat status absensi hari ini dan histori.
- Mengajukan izin/sakit (upload surat).
- Mengubah sebagian data profil milik sendiri.
- Tidak bisa melihat profil siswa lain.

---

## 3) Cara Menjalankan Aplikasi (Step by step)

### A. Instal dan konfigurasi (sekali saja)
1) Install dependency:
```bash
composer install
npm install
```

2) Siapkan .env:
```bash
copy .env.example .env
php artisan key:generate
```

Kalau pakai PowerShell, kamu bisa juga:
`Copy-Item .env.example .env`

### B. Reset DB + isi data XI RPL 1 (wajib untuk demo)
```bash
php artisan migrate:fresh --seed
```

### C. Jalankan server + frontend
Terminal 1 (Laravel):
```bash
php artisan serve
```

Terminal 2 (Vite / asset dev):
```bash
npm run dev
```

Buka di browser laptop:
- http://localhost:8000

Kalau ESP8266 / HP butuh akses ke server dari jaringan (LAN / hotspot), jalankan server seperti ini:
```bash
php artisan serve --host 0.0.0.0 --port 8000
```

Lalu:
1) Cek IPv4 laptop/server (Windows): jalankan `ipconfig`, lihat bagian adapter Wi-Fi -> `IPv4 Address` (contoh: `192.168.43.6`)
2) Pastikan ESP8266 terhubung ke Wi-Fi/hotspot yang sama
3) Akses dari device lain: `http://<IP_LAPTOP>:8000`
4) Pastikan firewall mengizinkan port `8000` (kalau tidak, ESP8266 tidak bisa akses API)

---

## 4) Akun Demo yang Tersedia

Password default semuanya: `password`

### Admin
- username: `admin`

### Students (35 siswa)
- username: `s001` s/d `s035`
- sekretaris kelas: `s035` (punya role student + secretary)

### Teachers (13 guru)
- `t014`, `t016`, `t045`, `t050`, `t054`, `t062`, `t071`, `t073`, `t079`, `t080`, `t081`, `t083`, `t085`

Catatan penting:
- Kelas XI RPL 1 dipasang wali kelas = t014.
- Kalau login guru lain dan halaman My Classes kosong, artinya dia belum jadi wali kelas (belum di-assign).

---

## 5) Cara Pakai (User Flow Harian)

### A. Check-in (pagi)
1) Siswa tempel kartu RFID.
2) Alat mengirim UID ke server.
3) Kalau jam sesuai window check-in, server menyimpan check-in dan status present.

### B. Check-out (pulang)
1) Siswa tempel kartu RFID lagi.
2) Kalau jam sesuai window check-out, server menyimpan check-out.

### C. Kalau siswa izin/sakit
1) Siswa login -> My Attendance -> klik Request Permission/Sick.
2) Isi tanggal + tipe + alasan + upload foto surat.
3) Status permohonan menjadi pending.
4) Sekretaris login -> Absence Requests -> approve/reject.
5) Kalau approve: sistem otomatis mengisi attendance_records untuk setiap tanggal pada rentang (status excused atau sick).

### D. Manual attendance dan early checkout oleh sekretaris
Sekretaris bisa:
- Set status manual (present/late/absent/excused/sick) + catatan.
- Early checkout (pulang sebelum jam normal) untuk siswa yang sudah check-in tapi belum check-out.

### E. Test kartu RFID (Admin)
Admin bisa uji kartu tanpa mengubah absensi:
1) Admin buka RFID Cards -> Create.
2) Pilih device aktif, klik Start Listening.
3) Tunggu device masuk mode **PEEK (listening)** (biasanya < 15 detik, tergantung health check).
   - Di Serial Monitor biasanya muncul log: `Device mode changed -> PEEK (listening)`.
4) Tap kartu di alat.
   - Saat listening, alat akan POST ke endpoint `/api/rfid/peek` (test mode) sehingga tidak mengubah absensi.
5) UID muncul di panel Live Capture dan bisa auto-fill input UID.
6) Jika kartu sudah terdaftar, panel menunjukkan pemiliknya.
7) Klik Stop untuk keluar dari listening (alat akan kembali ke mode scan).
8) Klik Clear untuk reset last scan.

---

## 6) Aturan Jam Absensi (Time Window)

Aturan jam ini tersimpan di database dan bisa diubah admin:
- Check-in: 05:45 - 07:10
- Check-out: 15:00 - 16:45

Kalau scan di luar jam:
- Sistem tidak mengubah record attendance.
- Sistem tetap menyimpan log event (buat audit).
- Response ke alat akan berisi kode OUTSIDE_WINDOW.

Timezone default: Asia/Jakarta.

---

## 7) Integrasi Hardware RFID (ESP8266 + MFRC522)

Bagian ini menjelaskan cara menyiapkan alat (NodeMCU ESP8266 + MFRC522) agar bisa:
- scan absensi normal (check-in/out)
- test UID (Live Capture / listening) tanpa mengubah absensi
- punya indikator LED + log serial yang jelas untuk debugging

### A. Hardware & wiring
Komponen:
- NodeMCU ESP8266
- MFRC522 RFID reader (wajib 3.3V)
- LED Putih (indikator ready), Hijau (sukses), Merah (error)
- Buzzer

Wiring MFRC522 -> NodeMCU (sesuai kode `arduino/esp8266_rfid_attendance.ino`):
- SDA (SS)  -> D8
- SCK       -> D5
- MOSI      -> D7
- MISO      -> D6
- RST       -> D3
- 3.3V      -> 3.3V
- GND       -> GND

LED & buzzer:
- LED Putih: D1
- LED Hijau: D2
- LED Merah: D0
- Buzzer: D4

Catatan penting:
- MFRC522 gunakan 3.3V (jangan 5V).
- Kalau alat sering restart saat buzzer bunyi, biasanya power supply kurang kuat.

### B. Library Arduino yang dibutuhkan
Pastikan Arduino IDE sudah bisa compile untuk ESP8266.

Library yang perlu di-install (Arduino Library Manager):
- `MFRC522`
- `ArduinoJson`

`ESP8266WiFi` dan `ESP8266HTTPClient` biasanya sudah ikut dari ESP8266 core.

### C. Konsep komunikasi (2 mode)
Alat punya 2 mode kerja:

1) **SCAN mode (normal absensi)**:
- POST `/api/rfid/scan`
- Mengubah `attendance_records` (check-in/out) sesuai time window.

2) **PEEK mode (test UID / Live Capture)**:
- POST `/api/rfid/peek`
- Hanya menyimpan UID terakhir per device (`rfid_last_scans`) untuk ditampilkan ke admin.
- Tidak mengubah `attendance_records`.

Header wajib untuk semua API device:
- `X-Device-Token: <token_device>`

Body minimal:
```json
{ "uid": "..." }
```

Server membalas JSON yang biasanya berisi:
- `ok` (true/false)
- `code` (misalnya `CHECKIN_OK`, `OUTSIDE_WINDOW`, `PEEK_OK`, dll)
- `message` (pesan singkat)

### D. Health check (cek API/DB + mode device)
Alat melakukan GET `/api/health` secara berkala (default tiap ~15 detik) untuk:
- memastikan token valid dan server hidup
- mengetahui mode device yang harus dipakai (`scan` atau `peek`)

Response contoh (dipotong):
```json
{
  "ok": true,
  "db": true,
  "mode": "scan"
}
```

### E. Cara kerja Live Capture (Start Listening)
Saat Admin klik **Start Listening** di halaman `RFID Cards -> Create`:
- Server mengaktifkan *capture mode* untuk device tersebut (sementara, timeout sekitar 10 menit atau sampai Stop).
- Alat akan membaca `mode` dari endpoint `/api/health`:
  - kalau `mode=peek` -> alat kirim UID ke `/api/rfid/peek`
  - kalau `mode=scan` -> alat kirim UID ke `/api/rfid/scan`

Jadi untuk Live Capture, kamu tidak perlu ganti `API_URL` manual (yang penting `HEALTH_URL` dan `PEEK_URL` benar dan token benar).

### F. Konfigurasi file Arduino (wajib)
Kode alat ada di:
- `arduino/esp8266_rfid_attendance.ino`

Yang wajib diubah sebelum upload:
- Buat file `arduino/secrets.h` (copy dari `arduino/secrets.example.h`)
  - isi `WIFI_SSID`, `WIFI_PASSWORD`, `DEVICE_TOKEN`
- `API_URL`, `PEEK_URL`, `HEALTH_URL`
  - harus pakai IP laptop/server, bukan `localhost`
  - contoh: `http://192.168.43.6:8000/api/rfid/scan`

Kalau IP laptop berubah (sering terjadi saat pakai hotspot), kamu harus update URL di Arduino.

### G. Device token (wajib untuk akses API)
Token device adalah "kunci" alat untuk akses API.

Buat token:
1) Admin -> Devices -> New Device
2) Setelah create, token bisa dilihat di halaman device (ikon mata). Copy dan simpan.

Kalau token hilang:
- Admin -> Devices -> Edit -> **Buat Ulang Token**
- Copy token baru, tempel ke `DEVICE_TOKEN` di `arduino/secrets.h`
- Token lama otomatis tidak berlaku

### H. Indikator LED (default)
LED Putih selalu ON sebagai indikator "alat nyala/ready".

Overlay (tanpa mematikan putih):
- Sukses (`ok=true`): hijau blink 2x + beep 1x
- Peringatan (`ALREADY_*` / `OUTSIDE_*`): kuning (hijau+merah) blink 2x + beep 2x
- Error (token salah / gagal konek / dll): merah blink 3x + beep 3x

Health indicator:
- API OK + DB OK: hijau blink 1x
- API OK tapi DB gagal: kuning blink 2x
- API gagal / WiFi off: merah blink 1x

### I. Serial Monitor (debugging)
Buka Serial Monitor dengan baud `115200`.

Yang akan terlihat:
- status WiFi (connected/disconnected)
- hasil health check (ok/db/mode)
- UID kartu saat terbaca
- request ke API (URL, body, HTTP code, response JSON)
- kalau token salah: log `UNAUTHORIZED: check DEVICE_TOKEN`

Ini sangat membantu kalau Live Capture "No scan yet" atau device tidak pernah dianggap connected.

### J. Kode response penting (yang sering muncul)
- `UNAUTHORIZED` -> token salah / device non-aktif
- `PEEK_OK` -> sukses peek UID (Live Capture)
- `CARD_NOT_REGISTERED` -> kartu belum dipasangkan ke siswa
- `NO_CLASSROOM` -> siswa belum masuk kelas (membership belum ada)
- `CHECKIN_OK` / `CHECKOUT_OK` -> sukses absensi
- `ALREADY_CHECKED_IN` / `ALREADY_CHECKED_OUT` -> sudah pernah scan
- `OUTSIDE_WINDOW` -> scan di luar jam absensi

### K. Status Connection di Admin -> Devices
Di halaman Devices ada status koneksi:
- **Never**: device belum pernah request yang lolos auth (biasanya token salah / server tidak bisa diakses)
- **Connected**: last_seen_at dalam 120 detik terakhir
- **Offline**: pernah konek tapi sudah > 120 detik

`last_seen_at` akan ter-update saat alat sukses request (scan/peek/health) dengan token yang valid.

---

## 8) Data Apa Saja yang Disimpan (Ringkas)

Anggap database itu lemari arsip. Semua kejadian dicatat agar bisa dilihat kembali.

Yang penting untuk dipahami:

### A. Users
Berisi akun login (admin, teacher, secretary, student).
- Login menggunakan username + password.
- Kalau user di-nonaktifkan, tidak bisa login.
- Locale user disimpan di kolom users.locale (untuk multi-language).

### B. Classrooms dan Membership
- classrooms = daftar kelas (contoh: XI RPL 1)
- classroom_memberships = siapa siswa yang masuk kelas itu, dan siapa sekretarisnya

### C. RFID Cards
Tabel rfid_cards menyimpan mapping:
- UID kartu RFID -> siswa pemilik kartu

### D. RFID Last Scans (Live Capture)
Tabel rfid_last_scans menyimpan UID terakhir per device:
- device_id, uid, scanned_at
- dipakai oleh Admin Live Capture

### E. Attendance Records (rekap harian)
Tabel attendance_records adalah rekap per hari per siswa:
- tanggal, status, jam check-in/out, metode (rfid/manual), dll.

### F. Attendance Events (log semua scan)
Tabel attendance_events mencatat semua scan termasuk yang ditolak.
Tujuan: audit (misal ada kartu gak terdaftar, scan di luar jam, dll).

### G. Absence Requests + Files
Permohonan izin/sakit:
- absence_requests = data permohonan
- absence_request_files = file surat (foto) yang diupload

File disimpan di storage/app/... dan hanya bisa di-download lewat route yang dicek policy (tidak public).

---

## 9) Keamanan dan Privasi (Kenapa aman untuk MVP)

Yang sudah diterapkan:
- Role-based access (Spatie Permission):
  - route admin hanya untuk role admin
  - route teacher hanya untuk role teacher
  - route secretary hanya untuk role secretary
  - route student hanya untuk role student
- Policy untuk scope kelas:
  - teacher hanya bisa lihat siswa di kelas yang dia pegang
  - secretary hanya bisa approve/kelola siswa di kelasnya
  - student hanya bisa lihat profil sendiri
- Device token auth untuk API RFID (alat harus punya token)
- Rate limit untuk scan RFID (mencegah spam request)
- File surat izin bersifat private (tidak bisa diakses tanpa login dan izin)
- Endpoint test /api/rfid/peek juga wajib token device.

---

## 10) Multi-language (ID/EN)

UI mendukung bahasa Indonesia dan English.
- Default bahasa: mengikuti `APP_LOCALE` di `.env` (default di repo: `en`)
- Switch bahasa ada di kanan atas (fixed) pada semua halaman.
- Jika login: pilihan disimpan di users.locale
- Jika belum login: disimpan di session

---

## 11) Troubleshooting (Kalau "kok gak jalan?")

### A. Guru login tapi My Classes kosong
Artinya guru itu belum di-set jadi wali kelas.
Solusi:
- Login admin -> Classrooms -> pilih kelas -> set Homeroom Teacher

### B. Scan RFID tapi selalu UNAUTHORIZED
- Pastikan header X-Device-Token benar.
- Pastikan device is_active = true di Admin -> Devices.

### C. Scan RFID tapi CARD_NOT_REGISTERED
Admin belum mendaftarkan UID kartu ke siswa:
- Admin -> RFID Cards -> New Card -> isi UID -> pilih siswa.

### D. Scan RFID tapi NO_CLASSROOM
Siswa belum punya membership kelas:
- Admin -> Classrooms -> Add Student.

### E. Scan RFID tapi OUTSIDE_WINDOW
- Cek jam sekarang dan window check-in/out.
- Pastikan timezone Asia/Jakarta (Admin -> Attendance Settings).

### F. Live Capture tidak update
- Pastikan device aktif.
- Pastikan `DEVICE_TOKEN` di `arduino/secrets.h` benar (kalau salah, akan selalu UNAUTHORIZED).
- Pastikan `API_URL/PEEK_URL/HEALTH_URL` pakai IP laptop/server (bukan localhost) dan port `8000` tidak diblok firewall.
- Klik Start Listening dan tunggu sampai Serial Monitor muncul `Device mode changed -> PEEK (listening)`.
- Pastikan admin memilih device yang benar di dropdown.

### G. Devices -> Connection tetap "Never" atau "Offline"
- "Never" artinya device belum pernah request yang lolos auth (token salah / belum terhubung WiFi / IP salah / firewall).
- Pastikan server dijalankan pakai `--host 0.0.0.0` dan port `8000` terbuka.
- Cek Serial Monitor: harus ada log health check yang sukses (HTTP 200) dan bukan 401.

### H. Start Listening sudah ON tapi device masih kirim ke scan (absensi)
- Cek Serial Monitor: health check harus menampilkan `mode=peek` saat listening.
- Pastikan `HEALTH_URL` benar dan bisa diakses dari ESP8266.
- Jika pakai hotspot, IP laptop bisa berubah, update URL di Arduino.

---

## 12) Penjelasan Teknis (untuk yang mau ngoprek)

Bagian ini menunjuk di file mana logic tertentu berada, supaya kamu gampang cari.

### Routing
- Web routes: routes/web.php
- API routes: routes/api.php

### Auth (login pakai username)
- Request login: app/Http/Requests/Auth/LoginRequest.php

### RFID API (inti sistem)
- Controller scan: app/Http/Controllers/Api/RfidScanController.php
  - validasi device token
  - cari kartu aktif
  - cari membership kelas aktif
  - tentukan check-in/check-out berdasarkan jam
  - simpan attendance_records
  - log attendance_events

- Controller peek: app/Http/Controllers/Api/RfidPeekController.php
  - validasi device token
  - simpan UID terakhir per device (rfid_last_scans)
  - tidak mengubah attendance_records

- Health check: app/Http/Controllers/Api/HealthController.php
  - cek koneksi DB
  - mengembalikan `mode` (scan/peek) untuk device

### Device auth middleware
- app/Http/Middleware/DeviceTokenAuth.php
  - baca header X-Device-Token
  - hash sha256
  - cari device aktif
  - update `devices.last_seen_at` (untuk status Connected/Offline di halaman Devices)

### Live Capture mode toggle (Start/Stop Listening)
- app/Http/Controllers/Admin/RfidLiveCaptureController.php
  - start/stop capture mode per device (disimpan di cache)
- UI: resources/views/admin/rfid-cards/create.blade.php
  - tombol Start/Stop memanggil endpoint toggle capture mode

### Rate limit untuk scan
- app/Providers/AppServiceProvider.php
  - limit 120 request/menit per device

### Absence request (upload surat)
- Student submit: app/Http/Controllers/Me/AbsenceRequestController.php
- File download (private): app/Http/Controllers/AbsenceRequestFileController.php
- Secretary approve/reject: app/Http/Controllers/Secretary/AbsenceRequestController.php

### Manual attendance dan early checkout
- app/Http/Controllers/Secretary/AttendanceController.php

### Teacher attendance dan export
- app/Http/Controllers/Teacher/ClassroomController.php
  - export saat query ?export=excel (CSV semicolon + BOM agar rapi di Excel)

### Policies (privacy dan scope)
- app/Policies/StudentProfilePolicy.php
- app/Policies/AbsenceRequestPolicy.php
- Registry policy + admin override: app/Providers/AuthServiceProvider.php

### Locale middleware
- app/Http/Middleware/SetLocale.php
- app/Http/Controllers/LocaleController.php

### UI (Blade + Tailwind + Alpine)
- Layout utama (sidebar/topbar): resources/views/layouts/app.blade.php
- Sidebar: resources/views/layouts/partials/sidebar.blade.php
- Theme CSS: resources/css/app.css
- Live Capture panel: resources/views/admin/rfid-cards/create.blade.php

### Seeder data XI RPL 1
- database/seeders/DatabaseSeeder.php
  - reset data
  - buat roles
  - buat admin
  - buat kelas XI RPL 1
  - seed 35 siswa (s001-s035)
  - seed 13 guru (t0xx)
  - set wali kelas XI RPL 1 = t014

---

## 13) Batasan MVP (supaya kamu paham "kenapa begini")

Beberapa hal dibuat sederhana dulu (MVP):
- Teacher hanya bisa melihat kelas yang dia jadi wali kelas (belum ada tabel assignment mapel).
- Status izin/sakit yang sudah approved akan mencegah check-in lewat RFID (mengurangi konflik data).
- Export Excel saat ini adalah CSV yang sudah dioptimalkan agar terbaca rapi di Excel (lebih ringan untuk MVP).

Kalau kamu mau naik level (next step), kita bisa tambah:
- tabel teacher_classrooms (guru mapel bisa pegang banyak kelas)
- export .xlsx asli dengan library
- notifikasi (pending request, dsb.)
