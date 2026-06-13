# Architecture: Generations, Target State, and Migration Plan

## The three generations

This codebase carries patterns from three eras of Laravel. Recognising which generation a file
belongs to tells you how much to trust it and how to extend it.

### Generation 1 — Laravel 4/5 era (Build Brighton inheritance, ~2014–2015)

| Pattern | Where | Status |
| --- | --- | --- |
| Standalone validator classes | `app/Validators/` (e.g. `FormValidator`, `ProfileValidator`, `RoleValidator`) | Superseded by FormRequests; still used by older controllers |
| Command/event handlers | `app/Handlers/` (`PaymentEventHandler`, `SubChargeEventHandler`) | Still load-bearing for billing! Wired via string events (`payment.paid`, `payment.cancelled`, `sub-charge.payment-failed`) in `EventServiceProvider` |
| Presenters | `app/Presenters/` + `laracasts/presenter` | View formatting; replace with accessors |
| Facade-heavy controllers | `\Request::`, `\Auth::`, `\View::make()` throughout older controllers | Cosmetic but dates the code |
| String-based events | `\Event::fire('payment.paid', ...)`-style listeners | Modern equivalent is event classes |
| Repositories as the data layer | `app/Repo/` (~15 classes) | Mixed: some genuinely abstract queries, some are pass-through noise |

### Generation 2 — Laravel 5.5–7 era

- Eloquent models in `app/Entities/` with observers (`UserObserver`, `UserAuditObserver`)
- Route-middleware role checks (`role:admin` etc. via `HasRole`)
- Blade layouts + jQuery for UI

### Generation 3 — current practice (the style all new code uses)

- **FormRequests carrying authorization**: e.g. `TrainInductionRequest::authorize()` calls the
  policy — validation and authz in one place
- **Policies** (`app/Policies/`, 9 classes) with `before()` admin short-circuits and
  `authorizeResource()` in constructors
- **Event classes + listeners** (`app/Events/Inductions/*`, `app/Listeners/`)
- **Queued jobs** (`DiscourseSync`)
- **Service objects** (`app/Services/MemberSubscriptionCharges`, `Credit`)
- **Inertia + React + TypeScript** pages (see frontend.md)
- Feature tests asserting state + events (see testing.md)

The course/equipment-training subsystem is the reference implementation of generation 3 — when in
doubt about "how should this look", read `CourseController`, `CoursePolicy`,
`TrainInductionRequest`, `CourseTrainingController`, and their tests.

## Target architecture

Keep it boring and native — this is a small-team community project, not a DDD showcase:

- **Controllers**: thin; resolve a FormRequest (validation + authorization), call a service or the
  model, return an Inertia response.
- **Authorization**: policies only. No hand-rolled role checks in controllers; route middleware as
  defense-in-depth, never the sole gate.
- **Domain logic**: service classes for multi-step operations (billing already works this way);
  plain Eloquent for simple CRUD — a repository that only proxies `Model::find()` should be
  deleted, not preserved.
- **Side effects**: event classes + queued listeners. Pull the email/Telegram side effects out of
  `UserObserver` into listeners over time so the observer only *detects* transitions and fires
  events.
- **Money**: integer pence everywhere (see project-health.md).

## Migration plan

The rule that has already been working in this repo: **modernise what you touch, don't big-bang.**
Each step below is independently shippable.

### Step 0 — prerequisites (do first)

1. Pin current behavior with tests around the areas being changed (see testing.md).
2. Remove BrowserKit tests + the `laravel/browser-kit-testing` dependency.

### Step 1 — the framework ladder (the strategic move)

Laravel 7 → 8 → 9 → 10 → 11, one PR per hop, CI green at each rung. Per-hop highlights:

- **7→8**: class-based factories (mechanical but touches every test), `Models` namespace optional
  (keep `Entities` to reduce churn), PHP 7.3+.
- **8→9**: PHP 8.0+, Symfony 6, `fideloper/proxy` and `fruitcake/laravel-cors` deleted (built in),
  Flysystem 3.
- **9→10**: PHP 8.1+, native type declarations land across the skeleton.
- **10→11**: PHP 8.2+, slim skeleton (optional adoption), per-second rate limiting.

Update the Dockerfile PHP version in lockstep; bump Node to current LTS at the same time. After
the ladder: adopt **Sanctum** to modernise API token auth, and replace `fzaninotto/faker` with
`fakerphp/faker`.

### Step 2 — kill generation 1, subsystem by subsystem

In order of value:

1. **Billing handlers** (`app/Handlers/*`): convert the string events to event classes
   (`PaymentPaid`, `PaymentCancelled`, `SubscriptionChargePaymentFailed`) with listeners; the
   existing handler tests port across nearly 1:1. Highest value because this is the money path and
   string events are invisible to static analysis.
2. **Validators** (`app/Validators/`): each one becomes a FormRequest on the controller that uses
   it; delete the class. Do this whenever a controller is touched for any other reason.
3. **Repositories**: split into (a) genuinely useful query objects — keep (e.g.
   `SubscriptionChargeRepository` has real logic), and (b) pass-throughs — inline into
   controllers/services and delete.
4. **Presenters**: replace with Eloquent accessors/casts when the relevant views are converted to
   Inertia — Inertia resources make presenters redundant anyway.

### Step 3 — shrink the god classes

- `app/Entities/User.php` (~561 lines): extract concerns into traits/services along its natural
  seams — payment/subscription methods, role methods (already partly in `UserRoleTrait`), and the
  lifecycle transitions (`setPaymentWarning`, `setSuspended`, …) into a guarded `MembershipStatus`
  service or small state machine.
- `AccountController` (~557 lines): split into Registration, Profile (admin), and Subscription
  settings controllers as those pages go Inertia.

### Step 4 — ratchet quality

- Continue burning down `phpstan-baseline.neon` (down from ~200 to 26): the stale-docblock and
  genuine-bug tranches are done; the remainder is mostly `Authenticatable` property noise that the
  framework upgrade fixes for free, plus a few larastan relation false-positives. Raise to level
  6–7 once under ~20 entries, and add a CI ratchet so the baseline can't grow.
- Add `declare(strict_types=1)` + native parameter/return types to files as they're touched.

## How to sequence against other work

The framework ladder (step 1) and the frontend migration (frontend.md) are independent tracks. The
generation-1 cleanup (step 2) is best done *after* 7→8/9 because the upgrade mechanically touches
the same files (factories, events) — doing it once avoids double churn.
