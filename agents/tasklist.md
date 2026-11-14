# Tasklist — TempChat

Progress: 100%

Referensi: `.agents/prd.md`, `.agents/design.md`, `.agents/task-instruction.md`

## 1. Project Setup & Configuration

- [x] ✅ Task 1.1 - Konfigurasi theme Tailwind v4 sesuai design.md `[Sedang]` (Selesai)
  - `resources/css/app.css`: token warna/radius/font Zed Dev + komponen `.card`, `.field`, `.btn-primary`, `.btn-ghost`, `.label`
- [x] ✅ Task 1.2 - Konfigurasi font (JetBrains Mono + Inter) dan layout dasar `[Sedang]` (Selesai)
  - `vite.config.js` (bunny fonts), `resources/views/layouts/app.blade.php`, `config/app.php` (timezone Asia/Jakarta)

## 2. Database & Migration

- [x] ✅ Task 2.1 - Membuat migration `rooms` `[Mudah]` (Selesai)
  - `database/migrations/2026_11_14_000001_create_rooms_table.php`
- [x] ✅ Task 2.2 - Membuat migration `room_users` `[Mudah]` (Selesai)
  - `database/migrations/2026_11_14_000002_create_room_users_table.php`
- [x] ✅ Task 2.3 - Membuat migration `messages` `[Mudah]` (Selesai)
  - `database/migrations/2026_11_14_000003_create_messages_table.php` (FK cascade)

## 3. Models & Relationships

- [x] ✅ Task 3.1 - Model `Room` + relasi `[Mudah]` (Selesai)
  - `app/Models/Room.php`: generateCode, isExpired, onlineCount, route key `code`
- [x] ✅ Task 3.2 - Model `RoomUser` + relasi `[Mudah]` (Selesai)
  - `app/Models/RoomUser.php`: uniqueName (suffix duplikat), isOnline
- [x] ✅ Task 3.3 - Model `Message` + relasi `[Mudah]` (Selesai)
  - `app/Models/Message.php`

## 4. Backend Logic & API

- [x] ✅ Task 4.1 - Homepage + create room `[Sedang]` (Selesai)
  - `HomeController` + `resources/views/home.blade.php`
- [x] ✅ Task 4.2 - Join room + dedupe nama `[Sedang]` (Selesai)
  - `RoomController@join` + `resources/views/room/join.blade.php`
- [x] ✅ Task 4.3 - Halaman chat + endpoint polling pesan `[Sedang]` (Selesai)
  - `RoomController@show`, `MessageController@index`, `resources/views/room/show.blade.php`
- [x] ✅ Task 4.4 - Kirim pesan + rate limit `[Sedang]` (Selesai)
  - `MessageController@store` + `RateLimiter` 1 pesan/detik
- [x] ✅ Task 4.5 - Typing indicator (POST update + GET status read-only) `[Sedang]` (Selesai)
  - `app/Http/Controllers/TypingController.php`
- [x] ✅ Task 4.6 - Leave room + system message `[Mudah]` (Selesai)
  - `RoomController@leave` + modal konfirmasi
- [x] ✅ Task 4.7 - Middleware cek room expired `[Mudah]` (Selesai)
  - `app/Http/Middleware/EnsureRoomIsActive.php` (HTTP 410), alias `room.active` di `bootstrap/app.php`

## 5. Security (Input Sanitization, Rate Limiting, XSS Protection, CSRF)

- [x] ✅ Task 5.1 - Sanitasi input nama/pesan dan escape output `[Sedang]` (Selesai)
  - `app/Support/Text.php` (strip_tags + squish + limit); render via `textContent` di JS
- [x] ✅ Task 5.2 - Validasi panjang + CSRF pada semua request `[Mudah]` (Selesai)
  - Validasi nama 30 / pesan 500, CSRF meta + `X-CSRF-TOKEN`

## 6. Frontend & Blade Views (Ikuti design.md)

- [x] ✅ Task 6.1 - Layout + homepage `[Sedang]` (Selesai)
- [x] ✅ Task 6.2 - Halaman join `[Mudah]` (Selesai)
- [x] ✅ Task 6.3 - Halaman chat (bubble, countdown, online, empty state) `[Sulit]` (Selesai)
  - `resources/views/room/show.blade.php`
- [x] ✅ Task 6.4 - Halaman room expired `[Mudah]` (Selesai)
  - `resources/views/room/expired.blade.php`

## 7. JavaScript & Realtime Features

- [x] ✅ Task 7.1 - Polling pesan 2 detik + smart scroll `[Sulit]` (Selesai)
  - `resources/js/room.js`
- [x] ✅ Task 7.2 - Debounce typing 300ms + indikator `[Sedang]` (Selesai)
- [x] ✅ Task 7.3 - Toast, sound notification, copy link, share `[Sedang]` (Selesai)
  - Web Audio beep, localStorage toggle, share WA/Telegram

## 8. Additional Features (Sound Notification, Share, Toast, System Message, dll)

- [x] ✅ Task 8.1 - Integrasi semua fitur tambahan di UI `[Sedang]` (Selesai)

## 9. Cleanup Command & Scheduler

- [x] ✅ Task 9.1 - Command `rooms:cleanup` + jadwal scheduler `[Mudah]` (Selesai)
  - `app/Console/Commands/CleanupExpiredRooms.php`, `routes/console.php` (`everyMinute`)

## 10. Final Polish & Bug Fixes

- [x] ✅ Task 10.1 - Feature test alur utama `[Sulit]` (Selesai)
  - `tests/Feature/RoomLifecycleTest.php`, `ChatTest.php`, `CleanupExpiredRoomsTest.php` — 14 test lulus
- [x] ✅ Task 10.2 - Pint, build asset, verifikasi manual `[Mudah]` (Selesai)
  - `vendor/bin/pint`, `npm run build`, smoke test HTTP create/send/poll/typing
- [x] ✅ Task 10.3 - Instruksi menjalankan aplikasi di README `[Mudah]` (Selesai)
  - `README.md`
