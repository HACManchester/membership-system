# Testing: Coverage, Approaches, and Where Help Is Welcome

## Summary

~38 test files / ~295 test methods, in four distinct generations and quality bands:

| Band | Where | Count | Verdict |
| --- | --- | --- | --- |
| Legacy BrowserKit | `tests/*.php` (root: AccountTest, FinanceTest, HomepageTest, InductionTest, KeyFobTest, LoginTest, SignupTest) | 7 files, ~20 tests | Outdated; some not even running; retire |
| Modern feature tests | `tests/Feature/` | 6 files, ~75 tests | Good to excellent |
| Integration tests | `tests/integration/` | 2 files, ~15 tests | Excellent — the crown jewels |
| Unit tests | `tests/unit/` | ~20 files, ~120 tests | Good, especially billing |

**Strongest coverage:** the payment/membership lifecycle (creation → billing → failure → warning →
suspension → recovery → left) and the equipment/induction/course system.
**Biggest gaps:** the GoCardless webhook endpoint, member signup end-to-end, several admin flows,
and the entire JS frontend.

## Infrastructure

- `phpunit.xml`: SQLite `:memory:`, array mail/cache, sync queue, Telegram disabled. Fast and
  isolated; `RefreshDatabase` migrates per test. No DB setup needed to run the suite.
- Run via Docker: `docker compose exec laravel vendor/bin/phpunit`.
- Base classes: `tests/TestCase.php` (modern) and `tests/BrowserKitTestCase.php` (legacy — its
  existence is what keeps `laravel/browser-kit-testing` in composer).
- Factories: mixed generations. `database/factories/ModelFactory.php` uses the legacy
  `$factory->define()` closure style; newer factories (Payment, Course, Equipment…) are separate
  files. The Laravel upgrade will force conversion to class-based factories.
- CI (`.github/workflows/ci.yml`): composer + yarn install, ESLint, production build, Jest
  (configured but **zero JS test files exist**), PHPStan, PHPUnit.

## Quality conventions

**What good looks like here** (use these as templates):

- `tests/integration/PaymentFlowIntegrationTest.php` — end-to-end payment failure → grace period →
  suspension with time travel (`Carbon::setTestNow`) and DB-state assertions at every step.
- `tests/unit/Services/MemberSubscriptionChargesTest.php` (~15 tests) — mocks `GoCardlessHelper`,
  verifies Payment rows created with the right amounts/statuses/source IDs.
- `tests/unit/Policies/CoursePolicyTest.php` — exercises the maintainer/area-coordinator
  authorization matrix with realistic relationships.
- `tests/Feature/EquipmentTest.php` (30 tests) — access-state visibility, role-based
  authorization, induction lifecycle, `Event::fake()` assertions.

**Rules for new tests:**

- Feature tests assert **state** (DB rows, queued mail, fired events), not just status codes.
- Mock at the boundary (`GoCardlessHelper`, Guzzle), never internal services.
- Use `Carbon::setTestNow()` with a `tearDown` reset for anything time-sensitive.
- One behavior per test; name as `test_<actor>_<can/cannot>_<action>[_<condition>]`.

**Known weak spots to avoid imitating / worth fixing:**

- Status-200-and-text-presence assertions with no DB/state verification (`HomepageTest`).
- `tests/SignupTest.php` has test methods missing the `@test` annotation — **they don't run**.
- `tests/unit/SubscriptionChargeTest.php` uses the deprecated `expectsEvents()` API and asserts
  almost nothing.
- A few `x_test_`-prefixed disabled tests in the legacy `InductionTest` — dead intent.

## Per-subsystem coverage

| Subsystem | Coverage | Notes |
| --- | --- | --- |
| Payment lifecycle & status transitions | ✅ Excellent | Integration + handler + process tests |
| Scheduled billing commands | ✅ Good | `BillMembersTest`, `CreateTodaysSubChargesTest`, `CheckMembershipStatusTest` |
| Equipment / inductions / courses | ✅ Excellent | Feature tests + policy tests |
| Discourse sync | ✅ Good | Job payload + event→job dispatch tested |
| Mail | ✅ Good | `UserMailerTest` (queue assertions; templates not rendered) |
| Exception handling | ✅ Good | Telegram throttling tested |
| Keyfobs | 🟡 Partial | Legacy BrowserKit tests + 1 CSV export test |
| Storage boxes | 🟡 Partial | Repository queries only; claim/release controller untested |
| Balance / cash payments | 🟡 Partial | Recalculation tested; controllers untested |
| Member signup & onboarding | 🟡 Weak | 2 legacy tests; no email-confirmation or full-flow test |
| Profile updates | 🟡 Weak | View-only legacy test |
| Disciplinary | 🟡 Minimal | 2 tests; no authorization-denial or notification tests |
| GoCardless webhook endpoint | 🔴 None | Event handling and state transitions need a test harness |
| Middleware | 🔴 None | Covered only incidentally via feature tests |
| Roles admin / RoleUsersController | 🔴 None | |
| General induction | 🔴 None | |
| Newsletter, gifts, stats, leaderboard | 🔴 None | Lower risk |
| JS frontend | 🔴 None | Jest configured, zero tests |

## Improvement plan

### Phase 1 — highest value first

1. **A GoCardless webhook feature test** (`tests/Feature/GoCardlessWebhookTest.php`): each event
   type mutates the right Payment/SubscriptionCharge/User state, and re-delivery of the same event
   is harmless. This is the money path — the most valuable missing test in the repo.
2. Fix the silent failures: add missing `@test` annotations in `SignupTest` (or port it), delete
   the `x_test_` corpses and `SubscriptionChargeTest::it_works`.
3. Add middleware unit tests (`HasRole`, `IsTrusted`) pinning current behavior before any
   refactoring of authorization.

### Phase 2 — retire the legacy band

1. Port the unique coverage from the 7 root BrowserKit tests into `tests/Feature/` (login,
   signup, keyfob authorization, finance-page access control), then delete them,
   `BrowserKitTestCase.php`, and the `laravel/browser-kit-testing` + `symfony/dom-crawler`
   dev dependencies. This also unblocks the framework upgrade.
2. Add feature tests for the untested member-facing flows: signup → confirm email → general
   induction → fob registration (one end-to-end test buys a lot here), storage box claim/release,
   profile update (asserting `MemberDiscourseParamsChanged` fires).

### Phase 3 — raise the bar (ongoing)

1. Convert factories to class-based during the Laravel 8 upgrade step; add factory states for the
   common personas (active member, payment-warning member, trainer, admin) to cut setup noise.
2. Add admin-flow tests: role assignment (including asserting non-admins are denied),
   disciplinary authorization, notification email recipient resolution.
3. Start JS testing where logic lives: begin with pure functions/hooks in `resources/js`, then
   component tests for the course-training pages. Delete the Jest CI step or make it real — a
   green "test" step that runs nothing is worse than none.
4. Adopt a coverage floor in CI once the phase-1/2 tests land (e.g. fail under 60%, ratchet up),
   so coverage only moves one direction.
