# Testing: Coverage, Approaches, and Where Help Is Welcome

## Summary

~42 test files / 276 tests, in three quality bands (the legacy BrowserKit band was retired in
June 2026 — its coverage was ported to `tests/Feature/`):

| Band | Where | Count | Verdict |
| --- | --- | --- | --- |
| Modern feature tests | `tests/Feature/` | 19 files | Good to excellent |
| Integration tests | `tests/integration/` | 2 files | Excellent — the crown jewels |
| Unit tests | `tests/unit/` | 20 files | Good, especially billing |

**Strongest coverage:** the payment/membership lifecycle (creation → billing → failure → warning →
suspension → recovery → left), the GoCardless webhook endpoint, the equipment/induction/course
system, and the admin authorization flows (roles, disciplinary, storage, cash).
**Biggest remaining gaps:** member email-confirmation/onboarding end-to-end, the `BalanceController`
view, gifts/stats/leaderboard (lower risk), and the entire JS frontend.

## Infrastructure

- `phpunit.xml`: SQLite `:memory:`, array mail/cache, sync queue, Telegram disabled. Fast and
  isolated; `RefreshDatabase` migrates per test. No DB setup needed to run the suite.
- Run via Docker: `docker compose exec laravel vendor/bin/phpunit`.
- Base class: `tests/TestCase.php`. (The legacy `BrowserKitTestCase` and the
  `laravel/browser-kit-testing` + `symfony/dom-crawler` dev dependencies were removed in June 2026.)
- Factories: mixed generations. `database/factories/ModelFactory.php` uses the legacy
  `$factory->define()` closure style; newer factories (Payment, Course, Equipment…) are separate
  files. The Laravel upgrade will force conversion to class-based factories.
- CI (`.github/workflows/ci.yml`): composer + yarn install, ESLint, production build, PHPStan,
  PHPUnit. (There is no JS test step — none exist yet; a no-op step was removed.)

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
- `tests/Feature/EquipmentTest.php` (30 tests) — access-state visibility, role-based
  authorization, induction lifecycle, `Event::fake()` assertions.

**Rules for new tests:**

- Feature tests assert **state** (DB rows, queued mail, fired events), not just status codes.
- Mock at the boundary (`GoCardlessHelper`, Guzzle), never internal services.
- Use `Carbon::setTestNow()` with a `tearDown` reset for anything time-sensitive.
- One behavior per test; name as `test_<actor>_<can/cannot>_<action>[_<condition>]`.

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
| Payment lifecycle & status transitions | ✅ Excellent | Integration + handler + process tests |
| GoCardless webhook endpoint | ✅ Good | `GoCardlessWebhookTest`: signature verification, every event type's state chain, idempotency |
| Scheduled billing commands | ✅ Good | `BillMembersTest`, `CreateTodaysSubChargesTest`, `CheckMembershipStatusTest` |
| Equipment / inductions / courses | ✅ Excellent | Feature tests + policy tests |
| Notification emails | ✅ Good | `NotificationEmailTest`: admin/trainer authorization, recipient resolution, body escaping |
| Authorization middleware | ✅ Good | `HasRole` + `IsTrusted` unit tests pin current semantics |
| Discourse sync | ✅ Good | Job payload + event→job dispatch tested |
| Mail | ✅ Good | `UserMailerTest` (queue assertions; templates not rendered) |
| Exception handling | ✅ Good | Telegram throttling tested |
| Keyfobs | ✅ Good | `KeyFobTest` covers the view/add/mark-lost authorization matrix (self/other/admin) + induction gate; `KeyFobCsvTest` covers export. Access-code generation still untested |
| Balance / cash payments | 🟡 Partial | Recalculation + `CashPaymentTest` (admin records cash payment, non-admin denied); `BalanceController` view still untested |
| Member signup & onboarding | ✅ Good | `SignupTest` (registration POST) + `OnboardingTest` (end-to-end: register → confirm email → general induction → fob registration) |
| Profile updates | ✅ Good | `AccountAccessTest` covers the profile edit page + update |
| Disciplinary | ✅ Good | `DisciplinaryTest`: ban/unban happy paths + authorization denials and the self-ban guard |
| Roles admin / RoleUsersController | ✅ Good | `RoleManagementTest`: assign/remove, admin-grants-admin (pinned), non-admin denied |
| General induction | ✅ Good | `GeneralInductionTest`: correct/incorrect code, case-insensitivity, fob registration |
| Gifts, stats, leaderboard | 🔴 None | Lower risk |
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

Still open — feature tests for the untested member-facing flows: signup → confirm email → general
induction → fob registration end-to-end (`SignupTest` covers the registration POST but not email
confirmation) and a profile update asserting `MemberDiscourseParamsChanged` fires.

### Phase 3 — raise the bar (ongoing)

1. Convert factories to class-based during the Laravel 8 upgrade step; add factory states for the
   common personas (active member, payment-warning member, trainer, admin) to cut setup noise.
2. Add admin-flow tests: role assignment (including asserting non-admins are denied),
   disciplinary authorization.
3. Start JS testing where logic lives: begin with pure functions/hooks in `resources/js`, then
   component tests for the course-training pages, and reinstate a CI step once tests exist.
4. Adopt a coverage floor in CI (e.g. fail under 60%, ratchet up), so coverage only moves one
   direction.
