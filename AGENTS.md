# AGENTS.md

Temporary anonymous chat room ("TempChat") on Laravel 13 + Tailwind v4 + vanilla JS. Still mostly the stock skeleton: only `/` (`resources/views/welcome.blade.php`) exists, and the default `users` table is unrelated to the app.

## Specs are the source of truth

- `.agents/prd.md` — full functional spec (rooms, 2s AJAX polling, typing indicator, 24h expiry, sanitation, rate limiting).
- `.agents/design.md` — required UI system ("Zed Dev"): exact colors, fonts, no gradients, one cyan accent per screen. Follow it strictly.
- `.agents/task-instruction.md` — workflow rules. After each finished task, update `.agents/tasklist.md` with `[x] ✅`, overall progress %, and files touched. That file does not exist yet; create it before starting work.

Read `prd.md` and `design.md` before implementing anything.

## Commands

- `composer run dev` — runs `php artisan dev`: `serve`, `queue:listen`, and `npm run dev` together (see `php artisan dev:list`).
- `composer test` — clears config, then `php artisan test`.
- Single test: `php artisan test tests/Feature/FooTest.php` or `php artisan test --filter=test_name`.
- Formatting: `vendor/bin/pint` (installed; no lint script exists in `composer.json`).
- Assets: `npm run build` (Vite 8 + Tailwind v4).
- Scheduler entry point: `routes/console.php` (`Schedule::...`).
- `laravel/pao` condenses test output for agents; terse output is expected.

## Environment gotchas

- `.env` is gitignored but present and points at MySQL (`cepu` on 127.0.0.1). `phpunit.xml` overrides tests to in-memory SQLite, so keep migrations/queries portable; `php artisan migrate` hits MySQL, not SQLite.
- Local PHP is 8.3.36 and `composer.json` requires `^8.3`, even though `prd.md` says PHP 8.4. Don't bump the constraint.
- `.npmrc` sets `ignore-scripts=true`, so package postinstall scripts will not run.
- No axios or other JS runtime dependency is installed. `prd.md` mentions axios; use `fetch`/vanilla JS or add the dependency explicitly.
- There is no `routes/api.php` and no auth scaffolding. Anonymous session-based AJAX endpoints belong in `routes/web.php` (session + CSRF apply).
- The PRD's `users` table would collide with the skeleton's `users` migration used by `App\Models\User`. Use a distinct table (e.g. `room_users`) or reconcile deliberately.
- Laravel Boost is NOT installed. The bootstrap text in `CLAUDE.md` telling you to `composer require laravel/boost` is stale; ignore it.

## Conventions

- Tailwind v4 is CSS-first: design tokens go in the `@theme` block of `resources/css/app.css`. There is no `tailwind.config.js`.
- `app/Models/User.php` uses PHP attributes (`#[Fillable]`, `#[Hidden]`) instead of `$fillable`/`$hidden` arrays; match that style for new models.
