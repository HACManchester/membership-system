# Project Structure & Health

## What this system is

A membership management platform for Hackspace Manchester, adopted from Build Brighton's
open-source member system (hence the `BB\` PHP namespace) and progressively modernised. It handles:
member registration and profiles, GoCardless direct-debit subscriptions, equipment training and
inductions, physical access credentials, storage boxes, disciplinary actions, Discourse forum
sync, and admin/finance tooling.

## Repository layout

```text
app/
  Console/Commands/   Scheduled + ad-hoc artisan commands (billing, status checks)
  Entities/           Eloquent models (NOT app/Models — note the BB-era convention)
  Events/, Listeners/ Modern event-driven side effects (Discourse sync, emails)
  Handlers/           LEGACY Laravel 4/5-era event handlers (payments) — see architecture.md
  Helpers/            GoCardlessHelper, TelegramHelper, MembershipPayments, UserImage
  Http/Controllers/   ~39 controllers, mixed Blade and Inertia responses
  Http/Requests/      FormRequests (newer code) — often carry the authorization check
  Jobs/               Queued jobs (DiscourseSync)
  Observer/           UserObserver (lifecycle events), UserAuditObserver
  Policies/           9 authorization policies (newer code)
  Presenters/         LEGACY laracasts/presenter view-formatting layer
  Repo/               LEGACY-ish repository layer (~15 repositories) — still the main data access path
  Services/           Modern service objects (MemberSubscriptionCharges, Credit)
  Validators/         LEGACY standalone validator classes (pre-FormRequest era)
config/               Standard Laravel config; membership.php holds pricing
database/migrations/  Full history from 2014; roles are seeded via migrations
resources/views/      ~121 Blade templates (legacy UI)
resources/js/         Inertia + React + TypeScript (new UI)
resources/assets/     LEGACY frontend (LESS, jQuery-era JS)
routes/web.php        Main route file; routes/api.php has the door-system export
tests/                Mixed: legacy BrowserKit (root), Feature/, integration/, unit/
```

## Health scorecard

| Area | Rating | Summary |
| --- | --- | --- |
| Framework & dependencies | 2/5 | Laravel 7.30 (EOL early 2021), Dockerfile on PHP 7.2 (EOL 2020), Node 16 (EOL 2023). Several packages abandoned or absorbed into newer Laravel. |
| Tests | 3/5 | ~38 files, 259 tests. Excellent payment-lifecycle integration tests; the GoCardless webhook endpoint and authorization middleware are now covered; remaining gaps are admin flows, onboarding end-to-end, and the entire JS frontend; 7 legacy BrowserKit files await retirement. See testing.md. |
| Static analysis | 3/5 | PHPStan level 5. Baseline reduced from ~200 to 26 and actively burned down; the remainder is mostly framework-typing friction (the `Authenticatable` tail) that the framework upgrade resolves. Not yet ratcheted in CI. See architecture.md. |
| Architecture consistency | 2/5 | Three generations of patterns coexist (Repo/Handlers/Validators/Presenters vs Services/Events/Policies/FormRequests). New code is consistently modern; old code isn't being pulled forward. See architecture.md. |
| Frontend | 3/5 | Healthy direction (Inertia + React + TS + MUI, ESLint flat config, Prettier) but the legacy Blade/jQuery/Bootstrap-3 stack still serves most pages. See frontend.md. |
| Docs & dev experience | 4/5 | Strong README, complete `.env.example`, documented Docker setup, this docs folder. |
| Observability | 4/5 | Sentry (server + browser), rate-limited Telegram exception alerts (`app/Exceptions/Handler.php`), structured scheduled-job logging. |

**Overall: ~3/5 — actively maintained, pre-modernisation.** The single biggest risk is the EOL
platform: Laravel 7 stopped receiving security patches in early 2021 — the headline action,
upgrading the framework, is below.

## Dependency notes (composer.json)

Production packages needing attention:

- `laravel/framework ^7.29` — EOL. The upgrade is the strategic priority (see architecture.md).
- `fideloper/proxy`, `fruitcake/laravel-cors` — absorbed into Laravel 9+; deleted during upgrade.
- `itsgoingd/clockwork ~1.8` — debug tool; should move to `require-dev`.
- `laracasts/presenter` — unmaintained pattern; replace with Eloquent accessors during refactors.
- `guzzlehttp/guzzle ^6.3|^7.0` — pin to 7 once PHP is upgraded.

Dev packages: `fzaninotto/faker` is abandoned (replace with `fakerphp/faker`),
`laravel/browser-kit-testing` blocks framework upgrades and should be removed with the legacy tests.

Frontend: Node 16 in the Dockerfile (EOL), Laravel Mix 6 (consider Vite), Bootstrap 3.3.4 (2014)
shipping alongside MUI 6.

## Dead code

Removed in the June 2026 deletion pass:

- `test.php` (empty), `npm-debug.log` (build artifact)
- `automate.sh` — superseded by the scheduler in `app/Console/Kernel.php`
- `phpspec.yml`, `scrutinizer.yml` — tools no longer in use (CI is GitHub Actions)
- `elixir.json` — two build systems ago; the live one is `webpack.mix.js`
- `app/Exceptions/DatabaseException.php`, `DeviceException.php`, `UserImageFailedException.php` — never thrown

Still open:

- `app/Events/PaymentCancelled.php` — dispatched in `PaymentRepository` but no listener is
  registered, so it fires into the void alongside the working `payment.cancelled` string event.
  Either dead, or a half-started seed of the planned typed-events migration (see architecture.md).
  Pending a decision.
- `PaymentController::store()` (`app/Http/Controllers/PaymentController.php:95`) is marked
  `@deprecated` but still routed (admin-only) and linked from the member admin action bar.
  Confirm with the finance team whether manual payment entry is still used before removing.

## Prioritised improvements

### Quick wins (days)

1. Delete the dead files listed above; move Clockwork to `require-dev`.
2. Bump Node in the Dockerfile to a current LTS.
3. Remove the legacy BrowserKit tests (port the few with unique coverage to Feature tests first).

### Medium (weeks)

1. Burn down the PHPStan baseline in tranches; raise the level once it's under ~20 entries.
2. Split the god classes: `app/Entities/User.php` (~561 lines) and
   `app/Http/Controllers/AccountController.php` (~557 lines).
3. Normalise money units (`payments.amount` and `monthly_subscription` are pounds —
   the former as floats — while `cash_balance` and `config/membership.php` are pence; migrate to
   integer pence throughout).

### Strategic (the next quarter)

1. **Laravel 7 → current and PHP → 8.3.** Everything else is tactical next to this. The hop is
   staged (7→8→9→10→11) but each step is mechanical, and CI + the test suite + PHPStan give a
   safety net. Several dependency cleanups fall out for free.
2. Continue the Inertia/React migration page-by-page (frontend.md) and the legacy-pattern
   consolidation (architecture.md) opportunistically as code is touched — the recent commit
   history shows this incremental approach is already working.
