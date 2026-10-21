# Testing: Coverage, Approaches, and Where Help Is Welcome

## Summary

68 test files / 486 tests (October 2026), in three quality bands (the legacy BrowserKit band was
retired in June 2026 — its coverage was ported to `tests/Feature/`):

| Band | Where | Count | Verdict |
| --- | --- | --- | --- |
| Modern feature tests | `tests/Feature/` | 39 files | Good to excellent |
| Integration tests | `tests/integration/` | 2 files | Excellent — the crown jewels |
| Unit tests | `tests/unit/` | 26 files | Good, especially billing |
| Mailer tests | `tests/Mailer/` | 1 file | Queue assertions only — rendering is covered elsewhere |

**Strongest coverage:** the payment/membership lifecycle (creation → billing → failure → warning →
suspension → recovery → left), the GoCardless webhook endpoint, the equipment/induction/course
system, and the admin authorization flows (roles, disciplinary, storage, cash).
Every GET route is smoke-tested as guest, member, trusted member and admin, so no page can start
500ing unnoticed. **Biggest remaining gaps:** `GoCardlessHelper` against the real SDK (it is always
mocked — see below), pages that are only smoke-tested (gifts, stats, the balance page), and the
entire JS frontend.

## Infrastructure

- `phpunit.xml`: SQLite `:memory:`, array mail/cache, sync queue, Telegram disabled. Fast and
  isolated; `RefreshDatabase` migrates per test. No DB setup needed to run the suite.
- Run via Docker: `docker compose exec laravel vendor/bin/phpunit`.
- Base class: `tests/TestCase.php`. Shared test helpers live in `tests/Concerns/` (namespace
  `Tests\Concerns`) — e.g. `MakesGoCardlessExceptions`. (The legacy `BrowserKitTestCase` and the
  `laravel/browser-kit-testing` + `symfony/dom-crawler` dev dependencies were removed in June 2026.)
- **PHP notices, warnings and deprecations fail the suite.** The app no longer suppresses them
  (an `error_reporting()` mask in `AppServiceProvider` was removed in October 2026), and Laravel
  turns them into exceptions. A test that "passes" while reading a property off `null` is now a
  failing test — that's intended. Note Laravel 9 stops throwing on deprecations, so this has to be
  re-enabled explicitly in `phpunit.xml` at that upgrade step.
- Factories: mixed generations, all closure-style. `database/factories/ModelFactory.php` uses the
  legacy `$factory->define()` style; newer factories (Payment, Course, Equipment…) are separate
  files. Laravel 9 removes this API; the upgrade bridges it with `laravel/legacy-factories` and
  converts to class-based factories as a separate step.
- CI (`.github/workflows/ci.yml`): a matrix of the PHP version production runs and the next one the
  upgrade moves to. The next version is non-blocking (`continue-on-error`) until it goes green.
  Each leg runs composer + yarn install, ESLint, production build, PHPStan, PHPUnit. (There is no
  JS test step — none exist yet.)

## Quality conventions

**What good looks like here** (use these as templates):

- `tests/integration/PaymentFlowIntegrationTest.php` — end-to-end payment failure → grace period →
  suspension with time travel (`Carbon::setTestNow`) and DB-state assertions at every step.
- `tests/Feature/GoCardlessWebhookTest.php` — drives the real webhook endpoint with a valid HMAC
  signature, asserts each event type's resulting Payment/SubscriptionCharge/User state, and pins
  idempotency (duplicate delivery is harmless). Mocks only the outbound GoCardless API.
- `tests/unit/Services/MemberSubscriptionChargesTest.php` — mocks `GoCardlessHelper`, verifies
  Payment rows created with the right amounts/statuses/source IDs.
- `tests/unit/Policies/CoursePolicyTest.php` — exercises the maintainer/area-coordinator
  authorization matrix with realistic relationships.
- `tests/Feature/EquipmentTest.php` — access-state visibility, role-based authorization,
  induction lifecycle, `Event::fake()` assertions.
- `tests/Feature/KeyFobCsvContractTest.php` — a byte-exact contract test: pins headers, escaping
  and edge cases of output another system parses. The pattern to copy for any external contract.

**Rules for new tests:**

- Feature tests assert **state** (DB rows, queued mail, fired events), not just status codes.
- Mock at the boundary (`GoCardlessHelper`, Guzzle), never internal services.
- Use `Carbon::setTestNow()` with a `tearDown` reset for anything time-sensitive.
- One behavior per test; name as `test_<actor>_<can/cannot>_<action>[_<condition>]`.
- Build third-party objects the way the library does. GoCardless exceptions, for example, must be
  constructed from an API error envelope (`Tests\Concerns\MakesGoCardlessExceptions`), not
  `new ValidationFailedException('message')` — the SDK reads `$response->body->error`, and the
  string form only ever appeared to work because warnings were suppressed.
- A new route needs nothing extra to be smoke-tested, but a new route **parameter** needs a fixture
  in `RouteSmokeTest::parameterValue()` — the test fails with instructions if one is missing.

**Webhook payload fidelity:** the GoCardless webhook tests use hand-crafted payloads whose shape
matches GoCardless's documented webhook envelope. That's robust for behavior and regression
coverage, but a *new* event-type handler should be backed by a captured real payload (the format
gap only bites for event shapes we've never received live traffic for).

**Known weak spots to avoid imitating:**

- Status-200-and-text-presence assertions with no DB/state verification (`HomepageTest`, and most
  of the legacy BrowserKit band).

## Per-subsystem coverage

| Subsystem | Coverage | Notes |
| --- | --- | --- |
| Every GET route (smoke) | ✅ Good | `RouteSmokeTest`: every GET route as guest/member/trusted/admin must not 5xx. Picks up new routes automatically; four skips, each justified in the file |
| Payment lifecycle & status transitions | ✅ Excellent | Integration + handler + process tests |
| Money arithmetic | ✅ Good | `MoneyHandlingTest`: float-pounds → integer-pence round-trips for awkward amounts, grace periods per payment method. Pins a known wart (a debt under £1 reports as positive) rather than fixing it |
| GoCardless SDK (`GoCardlessHelper`) | 🔴 None | Always mocked. The SDK upgrade needs sandbox integration tests first — see the upgrade plan |
| GoCardless webhook endpoint | ✅ Good | `GoCardlessWebhookTest`: signature verification, every event type's state chain, idempotency |
| Scheduled billing commands | ✅ Good | `BillMembersTest`, `CreateTodaysSubChargesTest`, `CheckMembershipStatusTest` |
| Equipment / inductions / courses | ✅ Excellent | Feature tests + policy tests |
| Notification emails | ✅ Good | `NotificationEmailTest`: admin/trainer authorization, recipient resolution, body escaping |
| Authorization middleware | ✅ Good | `HasRole` + `IsTrusted` unit tests pin current semantics |
| Discourse sync | ✅ Good | Job payload + event→job dispatch tested |
| Mail | ✅ Good | `UserMailerTest` (queue assertions); every Mailable and notification rendered via the preview routes in `RouteSmokeTest`; `MailRenderTest` covers multi-button notifications and the two raw-view sends, readable on SwiftMailer and Symfony Mailer alike |
| Exception handling | ✅ Good | Telegram throttling tested |
| Keyfobs | ✅ Good | `KeyFobTest` covers the view/add/mark-lost authorization matrix (self/other/admin) + induction gate; `KeyFobCsvTest` covers export and lockdown filtering; `KeyFobCsvContractTest` pins the format the door system parses. Access-code generation still untested |
| Balance / cash payments | 🟡 Partial | `MoneyHandlingTest` + `CashPaymentTest` (admin records cash payment, non-admin denied); the `BalanceController` page is smoke-tested only |
| Member signup & onboarding | ✅ Good | `SignupTest` (registration POST) + `OnboardingTest` (end-to-end: register → confirm email → general induction → fob registration) |
| Profile updates | ✅ Good | `AccountAccessTest` covers the profile edit page + update |
| Disciplinary | ✅ Good | `DisciplinaryTest`: ban/unban happy paths + authorization denials and the self-ban guard |
| Roles admin / RoleUsersController | ✅ Good | `RoleManagementTest`: assign/remove, admin-grants-admin (pinned), non-admin denied |
| General induction | ✅ Good | `GeneralInductionTest`: correct/incorrect code, case-insensitivity, fob registration |
| Gifts, stats, leaderboard | 🟡 Smoke only | Pages render without error; no behaviour assertions. Lower risk |
| JS frontend | 🔴 None | Jest is configured (`jest.config.js`) but no tests exist and it is not in CI |

## Improvement plan

### Phase 1 — pin the risky behavior ✅ done (June 2026)

The GoCardless webhook feature test, the `HasRole`/`IsTrusted` middleware tests, and the
test-honesty fixes (SignupTest now runs; `SubscriptionChargeTest` rebuilt on `Event::fake()`; the
`x_test_` stubs and the no-op Jest CI step removed) all landed. The money path and the
authorization middleware now have protective coverage ahead of the planned refactors.

### Phase 2 — retire the legacy band ✅ done (June 2026)

The 7 root BrowserKit tests were ported to `tests/Feature/` (`KeyFobTest`, `AccountAccessTest`,
`FinanceAccessTest`, `LoginTest`, `SignupTest`, `HomepageTest` — the induction one was already
covered by `Feature/InductionTest`), and `BrowserKitTestCase.php` plus the
`laravel/browser-kit-testing`, `symfony/dom-crawler`, and `symfony/css-selector` dev dependencies
were removed. This clears a Laravel-8-upgrade blocker.

Still open — a profile update asserting `MemberDiscourseParamsChanged` fires. (The end-to-end
onboarding flow has since been covered by `OnboardingTest`.)

### Pre-upgrade hardening ✅ done (October 2026)

Ahead of the Laravel 7 → 12 / PHP 7.2 → 8.4 upgrade, aimed at what a framework or engine upgrade
actually breaks:

- Removed the app-wide warning/notice suppression and fixed the latent nulls it was hiding.
- `RouteSmokeTest` — every GET route as every persona; found and fixed three pre-existing 500s.
- `KeyFobCsvContractTest` — the door-system export format, byte for byte.
- `MoneyHandlingTest` — float/pence round-trips and grace periods ahead of PHP 8's comparison and
  float-to-int changes.
- `MailRenderTest` — the mail paths most exposed to the Symfony Mailer swap.
- CI runs the next PHP version alongside the current one.

### Phase 3 — raise the bar (ongoing)

1. Convert factories to class-based once the upgrade has passed Laravel 9 (they're bridged with
   `laravel/legacy-factories` until then); add factory states for the common personas (active
   member, payment-warning member, trainer, admin) to cut setup noise.
2. Sandbox integration tests for `GoCardlessHelper`, before the GoCardless SDK is upgraded.
3. Start JS testing where logic lives: begin with pure functions/hooks in `resources/js`, then
   component tests for the course-training pages, and reinstate a CI step once tests exist.
4. Adopt a coverage floor in CI (e.g. fail under 60%, ratchet up), so coverage only moves one
   direction.
