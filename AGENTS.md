# AGENTS.md

TempChat: temporary anonymous chat room on Laravel 13 + MySQL + Tailwind v4 + vanilla JS. Implemented: create/join room via 8-char code, 2s AJAX polling, typing indicator, presence, 24h expiry, cleanup command.

## Specs and workflow

- `.agents/prd.md` — functional spec (rooms, polling, typing, expiry, sanitization).
- `.agents/design.md` — UI system ("Zed Dev"). Follow it strictly: warm near-black surfaces, single cyan accent, no gradients.
- `.agents/task-instruction.md` — after each finished task, update `.agents/tasklist.md` with `[x] ✅`, progress %, and files touched.
- Read `prd.md` and `design.md` before changing behavior or UI.

## Commands

- `composer run dev` — runs `php artisan dev`: `serve`, `queue:listen`, and `npm run dev` together.
- `composer test` — clears config, then `php artisan test`.
- Single test: `php artisan test tests/Feature/ChatTest.php` or `php artisan test --filter=test_name`.
- Formatting: `vendor/bin/pint`.
- Assets: `npm run build` (use `npm.cmd run build` if PowerShell blocks `npm.ps1`).
- Scheduler: `php artisan schedule:work`; cleanup command is `php artisan rooms:cleanup`.

## Environment gotchas

- App DB comes from `.env`: MySQL `cepu` on 127.0.0.1. Tests use MySQL database `cepu_test` (see `phpunit.xml`), which must exist before running tests:
  `CREATE DATABASE IF NOT EXISTS cepu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- This machine's PHP has no `pdo_sqlite`, so do not switch tests back to SQLite without installing the extension.
- `.npmrc` sets `ignore-scripts=true`, so package postinstall scripts will not run.
- No axios is installed. The frontend uses `fetch`; PRD mentions axios but that is superseded.
- No auth/login scaffold. Membership is session-based via `App\Support\RoomAuth`; anonymous AJAX endpoints live in `routes/web.php` (session + CSRF apply), not an `api.php`.
- Laravel Boost is NOT installed. The bootstrap text in `CLAUDE.md` telling you to install it is stale; ignore it.
- App timezone defaults to `Asia/Jakarta` in `config/app.php`, so `expired_at` / `HH:mm` render in WIB.

## Architecture notes

- `Room` binds routes by `code` (`getRouteKeyName`), not id. Use `{room}` in routes.
- Expired rooms return HTTP 410 via `EnsureRoomIsActive` (alias `room.active`) and render `room/expired.blade.php`.
- `rooms:cleanup` in `routes/console.php` runs every minute; DB foreign keys cascade to `room_users` and `messages`.
- `App\Support\Text::clean()` strips tags, collapses whitespace, and enforces max length. Message JSON is rendered client-side with `textContent`; never switch to `innerHTML`.
- PRD says own messages are violet, but `design.md` mandates a single accent, so own bubbles use the cyan tertiary. Keep the single-accent rule.
- Tailwind v4 is CSS-first: tokens live in the `@theme` block of `resources/css/app.css`; there is no `tailwind.config.js`.
- Models use PHP attribute configuration (`#[Fillable]`) instead of `$fillable` arrays.
