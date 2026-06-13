# CLAUDE.md

Membership management system for Hackspace Manchester (adopted from Build Brighton's open-source
system — hence the `BB\` namespace). Laravel 7 (mid-upgrade), MySQL, GoCardless billing, mixed
Blade + Inertia/React frontend.

**Read `docs/README.md` first** — it indexes documentation on project health, domain subsystems,
architecture, frontend, testing, and a glossary of domain terms.

## Running commands

The local dev environment is Docker (Laravel Sail-style, `docker-compose.yml`).

- **All PHP commands run inside the container** — prefix with `docker compose exec laravel`:

  ```sh
  docker compose exec laravel php artisan migrate
  docker compose exec laravel composer install
  docker compose exec laravel vendor/bin/phpunit
  docker compose exec laravel vendor/bin/phpstan analyse
  ```

- **Node/yarn commands run directly on the host** (no prefix):

  ```sh
  yarn dev        # Mix dev build
  yarn build      # production build
  yarn lint       # ESLint
  yarn test       # Jest (no JS tests exist yet)
  ```

Tests use SQLite in-memory (`phpunit.xml`) — no DB setup needed to run them.

## Where things live

- Models are in `app/Entities/` (not `app/Models/`).
- Routes: `routes/web.php` (everything) + `routes/api.php` (door-system export).
- Scheduled billing/status jobs: `app/Console/Kernel.php` — these are revenue-critical; read
  `docs/billing.md` before touching billing code.
- Legacy layers you'll encounter: `app/Repo/` (repositories), `app/Handlers/` (string-event
  handlers — still load-bearing for payments), `app/Validators/`, `app/Presenters/`. These are
  generation-1 patterns being retired — see `docs/architecture.md`.

## Conventions

- **Modernise what you touch, don't big-bang.** New/changed code uses: FormRequests that carry the
  authorization check in `authorize()` (calling a policy), policies for all authz, event classes +
  listeners for side effects, service objects for multi-step logic, Inertia + React + TypeScript
  for UI. The course/training subsystem (`CourseController`, `CoursePolicy`,
  `TrainInductionRequest`, `CourseTrainingController` + their tests) is the reference
  implementation — match it.
- **Authorization goes through policies**, not route middleware alone and not ad-hoc role checks
  in controllers. Route middleware is defense-in-depth, never the sole gate.
- No new Blade pages or jQuery; converting a Blade page you're working on to Inertia is preferred
  (`docs/frontend.md`).
- Feature tests assert DB state / queued mail / fired events, not just status codes. Mock at the
  boundary (`GoCardlessHelper`), never internal services.
- Use `$request->validated()`, never `$request->all()`, for writes.

## Gotchas

- **Money units are mixed**: `payments.amount` and `User.monthly_subscription` are **pounds**
  (payments as floats); `cash_balance` and `config/membership.php` are **pence**. Check units on
  every amount you handle.
- **Inertia props are sent to the browser in full**, rendered or not — shape props with dedicated
  Resources and include only what the page needs, especially for member data.
- The GoCardless webhook (`GoCardlessWebhookController`) is the only CSRF-exempt route and is
  signature-verified — keep it that way.
- The keyfob CSV export (`KeyFobCsvController`) is a de-facto contract with the door system —
  coordinate any output change with the acs team.
- `User.php` and `AccountController.php` are ~560-line god classes scheduled for splitting — avoid
  adding to them; extract instead.
