# RFID Attendance (MVP) - Laravel + JS

Sistem absensi multi-role untuk proyek IoT:
- RFID scan (ESP8266 + MFRC522 -> HTTP API) check-in/check-out dengan window waktu
- Izin/sakit via upload foto -> approval sekretaris
- Guru hanya melihat rekap kehadiran per kelas
- Student melihat histori & status harian

## Tech Stack
- Laravel (Breeze: Blade + Tailwind + Alpine)
- `spatie/laravel-permission` (roles)

## Setup (local)
```bash
composer install
npm install

copy .env.example .env
php artisan key:generate
```

## Run (wajib untuk MVP)
```bash
php artisan migrate:fresh --seed
php artisan serve
npm run dev
```

## Akun Demo (setelah seeding)
- Admin: `admin` / `password`
- Students: `s001` - `s035` / `password`
- Teachers: `t071`, `t081`, `t014`, `t073`, `t045`, `t079`, `t085`, `t016`, `t083`, `t050`, `t062`, `t080`, `t054` / `password`

## Hardware API (ESP8266 -> Laravel)
Endpoint:
- `POST /api/rfid/scan`

Auth header:
- `X-Device-Token: <token>`

Body:
```json
{ "uid": "CARD123", "scanned_at": "2026-02-03T06:00:00+07:00" }
```
`scanned_at` optional (default server time).

Catatan:
- Window waktu di **Admin -> Attendance Settings**
- Device token dibuat di **Admin -> Devices** (token bisa ditampilkan/sembunyikan di halaman device)

## Arduino Setup
- Copy `arduino/secrets.example.h` menjadi `arduino/secrets.h`
- Isi `WIFI_SSID`, `WIFI_PASSWORD`, `DEVICE_TOKEN` di `arduino/secrets.h`
- Pastikan `API_URL`, `PEEK_URL`, `HEALTH_URL` pakai IP laptop/server (bukan `localhost`)

## Dokumentasi lengkap (bahasa Indonesia)
- `docs/PANDUAN_SISTEM.md`
