# AGENTS.md

## Stack

Laravel 12 + Breeze on PHP 8.2. Server-rendered Blade with Alpine.js/Tailwind via Vite — no Inertia or SPA framework. Business logic lives in `app/Services/*Service.php`; controllers stay thin and validate through FormRequests in `app/Http/Requests`.

## Commands

- Tests: `php artisan test` (full suite runs in ~5s). Single class: `php artisan test --filter=TaskAssignmentNotificationTest`.
- Style: `vendor\bin\pint --test` to check, `vendor\bin\pint` to fix (Laravel preset, no config file). Several existing files currently fail the check — do not reformat unrelated files wholesale.
- Frontend build: `npm run build`; dev server: `npm run dev`.
- Full local stack in one terminal (serve + queue worker + pail logs + vite): `composer dev`. One-shot project setup: `composer setup`.

## Environment

- Windows/Laragon host. Prefer `vendor\bin\<tool>` and `php artisan ...` over Unix-style invocations.
- Dev runtime uses MySQL (`project_management` database per `.env`), but tests always use SQLite `:memory:` configured in phpunit.xml — no database or external services needed to run tests.
- Queue connection is `sync` under test; notifications are stored in the `notifications` table.

## Domain rules (easy to get wrong)

- Task statuses/priorities are plain capitalized strings, not enums: `Todo`, `In Progress`, `Review`, `Completed`, `Cancelled` / `Low`, `Medium`, `High`, `Critical`.
- `tasks.completed_at` is derived inside `TaskService` from `status === 'Completed'` — never set it directly elsewhere.
- Assignment notifications fire only when the assignee actually changes: `TaskService::update` → `TaskAssigned` event → `SendTaskAssignedNotification` listener → database notification.
- Tasks may only be assigned to members of their own project.
- Project roles are `Owner`/`Manager`/`Member` on `project_members`; the creator is auto-assigned `Owner`.
- Projects and tasks use soft deletes; project slugs are auto-generated and unique even against trashed rows (`ProjectService::uniqueSlug`).

## Reference

`README.md` documents features, routes, and manual testing steps — verify against code first; some "not built yet" claims have gone stale (notification mark-as-read UI and comment/attachment screens now exist).
