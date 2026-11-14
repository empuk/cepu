# TempChat

Temporary anonymous chat room: buat room, bagikan link-nya, ngobrol tanpa login. Room dan seluruh pesannya otomatis terhapus setelah 24 jam.

Dibangun dengan Laravel 13, MySQL, Tailwind CSS v4, dan Vanilla JavaScript (polling 2 detik).

## Fitur

- Create room dengan kode unik 8 karakter dan masa aktif 24 jam
- Join lewat `/room/{code}` tanpa register; nama duplikat otomatis diberi suffix
- Pesan realtime via AJAX polling, bubble kanan/kiri, waktu `HH:mm`
- Typing indicator (debounce 300ms, status read-only, otomatis hilang)
- Jumlah user online (aktif dalam 30 detik terakhir)
- Countdown masa aktif room, berubah merah saat tersisa < 1 jam
- System message join/leave, toast, sound notification, copy link, share WA/Telegram
- Sanitasi input, escape output, CSRF, dan rate limit 1 pesan/detik
- Command cleanup `rooms:cleanup` yang dijalankan scheduler tiap menit

## Kebutuhan

- PHP 8.3+
- Composer
- MySQL
- Node.js + npm

## Setup

```sh
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# Sesuaikan DB_* di .env, lalu:
php artisan migrate

npm install
npm run build
```

Database untuk test perlu dibuat sekali (default `cepu_test`, lihat `phpunit.xml`):

```sh
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS cepu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

## Menjalankan

```sh
composer run dev
```

Perintah itu menjalankan server, queue listener, dan Vite sekaligus. Alternatif manual:

```sh
php artisan serve
npm run dev
php artisan schedule:work
```

Buka `http://localhost:8000`.

## Scheduler cleanup

Room kedaluwarsa dihapus oleh command berikut (sudah dijadwalkan tiap menit di `routes/console.php`):

```sh
php artisan rooms:cleanup
php artisan schedule:work   # jalankan scheduler di terminal terpisah
```

## Testing & format

```sh
composer test
vendor/bin/pint
```

## Struktur utama

- `app/Http/Controllers` — HomeController, RoomController, MessageController, TypingController
- `app/Models` — `Room`, `RoomUser`, `Message`
- `app/Http/Middleware/EnsureRoomIsActive.php` — guard room expired (HTTP 410)
- `app/Console/Commands/CleanupExpiredRooms.php`
- `resources/views` — `home`, `room/join`, `room/show`, `room/expired`
- `resources/js/room.js` — polling, typing, countdown, toast, sound
- `.agents/prd.md` dan `.agents/design.md` — spesifikasi & panduan desain
