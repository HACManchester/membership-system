# Project Structure & Health

## What this system is

A membership management platform for Hackspace Manchester, adopted from Build Brighton's
open-source member system (hence the `BB\` PHP namespace) and progressively modernised. It handles:
member registration and profiles, GoCardless direct-debit subscriptions, equipment training and
inductions, physical access credentials, disciplinary actions, Discourse forum
sync, and admin/finance tooling.

## Repository layout

```text
app/
  Console/Commands/   Scheduled + ad-hoc artisan commands (billing, status checks)
  Entities/           Eloquent models (NOT app/Models — note the BB-era convention)
  Events/, Listeners/ Modern event-driven side effects (Discourse sync, emails)
  Handlers/           LEGACY Laravel 4/5-era event handlers (payments) — see architecture.md
  Helpers/            GoCardlessHelper, TelegramHelper, MembershipPayments, UserImage
  Http/Controllers/   ~44 controllers, mixed Blade and Inertia responses
  Http/Requests/      FormRequests (newer code) — often carry the authorization check
  Jobs/               Queued jobs (DiscourseSync)
  Observer/           UserObserver (lifecycle events), UserAuditObserver
  Policies/           11 authorization policies (newer code)
  Presenters/         LEGACY laracasts/presenter view-formatting layer
  Repo/               LEGACY-ish repository layer (~15 repositories) — still the main data access path
  Services/           Modern service objects (MemberSubscriptionCharges, Credit)
  Validators/         LEGACY standalone validator classes (pre-FormRequest era)
config/               Standard Laravel config; membership.php holds pricing
database/migrations/  Full history from 2014; roles are seeded via migrations
resources/views/      ~94 Blade templates (legacy UI)
resources/js/         Inertia + React + TypeScript (new UI)
resources/assets/     LEGACY frontend (LESS, jQuery-era JS)
routes/web.php        Main route file; routes/api.php has the door-system export
tests/                Feature/, Integration/, Unit/, Mailer/; shared helpers in Concerns/
```

## Health scorecard

| Area | Rating | Summary |
| --- | --- | --- |
| Framework & dependencies | 4/5 | Laravel 13 on PHP 8.4, Vite 8 on Node 20.19+, Dependabot proposing updates weekly. Still behind: Inertia 2, MUI 6, and the legacy Bootstrap 3 / jQuery / select2 stack. |
| Tests | 4/5 | 92 files, 649 tests. Excellent payment-lifecycle integration tests; every GET route smoke-tested; the GoCardless webhook endpoint and authorization middleware covered; any deprecation fails the suite. The JS frontend is barely tested. See testing.md. |
| Static analysis | 3/5 | PHPStan 2 at level 8, without level 6's missing-type checks. Baseline down from ~200 to 30 entries, all reviewed. Not yet ratcheted in CI. See architecture.md. |
| Architecture consistency | 2/5 | Three generations of patterns coexist (Repo/Handlers/Validators/Presenters vs Services/Events/Policies/FormRequests). New code is consistently modern; old code isn't being pulled forward. See architecture.md. |
| Frontend | 3/5 | Healthy direction (Inertia + React + TS + MUI, ESLint flat config, Prettier) but the legacy Blade/jQuery/Bootstrap-3 stack still serves most pages. See frontend.md. |
| Docs & dev experience | 4/5 | Strong README, complete `.env.example`, documented Docker setup, this docs folder. |
| Observability | 4/5 | Sentry (server + browser), rate-limited Telegram exception alerts (`app/Exceptions/Handler.php`), structured scheduled-job logging. |

**Overall: ~3.5/5 — actively maintained, on a current platform.** The framework upgrade is done
(October 2026). The biggest remaining risks are the legacy patterns and the Blade/jQuery pages,
both being retired as code is touched, and the production infrastructure upgrades below.

## Dependency notes (composer.json)

Laravel 13 on PHP 8.4 (`composer.json` requires `^8.4` and pins Composer's platform to it).
Upgraded or removed in October 2026: `fideloper/proxy` and `fruitcake/laravel-cors` (built into
Laravel), `fzaninotto/faker` (now `fakerphp/faker`), `laravel/legacy-factories`,
`michelf/php-markdown`, the GoCardless SDK (4.x → 8.x), Guzzle (7 → 8) and PHPUnit (13).
`itsgoingd/clockwork` and `laravel/browser-kit-testing` went in June 2026.

Still needing attention:

- `laracasts/presenter` — unmaintained pattern; replace with Eloquent accessors during refactors.
- `inertiajs/inertia-laravel` 2 — Inertia 3 has to move the PHP and JS sides together.

Frontend: Vite 8 on Yarn 4 (moved off Laravel Mix 6 in October 2026), Bootstrap 3 (2014) shipping
alongside MUI 6.

## Dependency updates (Dependabot)

Dependabot (`.github/dependabot.yml`) checks Composer, npm (Yarn) and GitHub Actions every
Monday morning. Releases have to be 7 days old before they're proposed; security updates skip this
wait. Yarn applies the same 7 days locally (`npmMinimalAgeGate` in `.yarnrc.yml`), so `yarn add` and
`yarn up` won't pick a newer release; list a package in `npmPreapprovedPackages` to take an urgent fix.

**Grouping:**

- Minor and patch updates are grouped per ecosystem, split into dev and production groups.
- `gocardless/gocardless-pro` always gets its own PR.
- Majors arrive one PR each.
- Security updates are grouped per ecosystem.

**Ignored majors.** These are held back because they're planned upgrades, or because the current
build or pages can't take them. Each rule in the file says what lifts it.

- Planned upgrades: `laravel/framework`, `phpunit/phpunit` and `@mui/*`.
- `gocardless/gocardless-pro`: needs a sandbox run.
- Inertia (`inertiajs/inertia-laravel` and `@inertiajs/*`): the PHP and JS sides must move
  together.
- Babel 8 (used only by Jest): it needs Node 22.18+, and local development is on Node 20.
- jQuery 4, Bootstrap 4 and Select2 4, until the legacy Blade pages are gone.
- TypeScript 7, until typescript-eslint supports it.
- ESLint 10 (`eslint`, `@eslint/js`), until `eslint-plugin-react` supports it.
- `@testing-library/jest-dom` 7, which needs Node 22.

**Merging.** Nothing merges automatically. Every Dependabot PR needs the code owner's review
(`.github/CODEOWNERS`) and "All checks passed", and is merged by hand at review time. Merging never
deploys: production deploys are always manual (see `operations.md`).

## Dead code

Removed in the June 2026 deletion pass:

- `test.php` (empty), `npm-debug.log` (build artifact)
- `automate.sh` — superseded by the scheduler in `app/Console/Kernel.php`
- `phpspec.yml`, `scrutinizer.yml` — tools no longer in use (CI is GitHub Actions)
- `elixir.json` — two build systems ago (Mix's `webpack.mix.js` followed it, and Vite replaced that)
- `app/Exceptions/DatabaseException.php`, `DeviceException.php`, `UserImageFailedException.php` — never thrown

Kept by decision:

- `app/Events/PaymentCancelled.php` — dispatched in `PaymentRepository` with no listener, but
  **kept intentionally** (June 2026) as a typed event for the core payment flow, to be wired to
  listeners during the typed-events migration (see architecture.md). Having events for the main
  payment lifecycle is worth more than the cost of an unconsumed dispatch.
- `PaymentController::store()` (`app/Http/Controllers/PaymentController.php`) — **kept** (June
  2026). It is marked `@deprecated`, but its `subscription` branch is live: the member admin page
  shows a "Record a £N Cash Subscription Payment" button for members whose
  `payment_method == 'cash'` (`member-admin-action-bar.blade.php`), recording a paid payment and
  extending membership a month. Its `door-key` and `storage-box` branches are unreachable dead
  code but are left in place alongside the live branch.

## Prioritised improvements

### Quick wins (days)

1. Type-check the frontend with `tsc` in CI.

### Medium (weeks)

1. Turn on PHPStan's missing-type checks (level 6) a file at a time.
2. Split the god class `app/Entities/User.php` (~585 lines). (`AccountController` has been split.)
3. Normalise money units (`payments.amount` and `monthly_subscription` are pounds —
   the former as floats — while `cash_balance` and `config/membership.php` are pence; migrate to
   integer pence throughout).
4. Inertia 3 (PHP and JS together), then the MUI majors.

### Strategic (the next quarter)

1. **Infrastructure, last.** Production MySQL 5.7 → 8.x and PHP 8.5 (CI already runs 8.5),
   deployment hardening, and swapping the local mysql and mailhog images.
2. Continue the Inertia/React migration page-by-page (frontend.md), retiring jQuery, Bootstrap 3
   and select2 with the last Blade pages that use them, and the legacy-pattern consolidation
   (architecture.md) opportunistically as code is touched — the recent commit history shows this
   incremental approach is already working.
