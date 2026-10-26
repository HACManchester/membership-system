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

- **FormRequests carrying authorization**: e.g. `TrainCourseMemberRequest::authorize()` calls the
  policy — validation and authz in one place
- **Policies** (`app/Policies/`, 11 classes) with `before()` admin short-circuits and
  `authorizeResource()` in constructors
- **Event classes + listeners** (`app/Events/Inductions/*`, `app/Listeners/`)
- **Queued jobs** (`DiscourseSync`)
- **Service objects** (`app/Services/MemberSubscriptionCharges`, `Credit`)
- **Inertia + React + TypeScript** pages (see frontend.md)
- Feature tests asserting state + events (see testing.md)

The course/equipment-training subsystem is the reference implementation of generation 3 — when in
doubt about "how should this look", read `CourseController`, `CoursePolicy`,
`TrainCourseMemberRequest`, `CourseTrainingController`, and their tests.

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

### Step 0 — prerequisites ✅ done (June 2026)

Behaviour around the areas being changed is pinned by tests (see testing.md), and the BrowserKit
tests and `laravel/browser-kit-testing` are gone.

### Step 1 — the framework ladder ✅ done (October 2026)

Laravel 7 → 13 and PHP 7.2 → 8.4, one PR per hop, CI green at each rung. Along the way:

- `fideloper/proxy` and `fruitcake/laravel-cors` went (Laravel has both built in). The trusted
  proxies are set by `TRUSTED_PROXIES` (`config/trustedproxy.php`).
- `fzaninotto/faker` became `fakerphp/faker`; PHPUnit is on 13, Symfony on 8.
- The factories were bridged with `laravel/legacy-factories`, then converted to class-based
  factories after the ladder reached Laravel 13.
- The GoCardless SDK moved from 4.x to 8.x, with idempotency keys on billing calls (billing.md),
  and Guzzle from 7 to 8.
- The test suite fails on every deprecation, including those raised in `vendor/`
  (`Tests\TestCase::knownVendorDeprecations()` lists any we can't fix; it's empty today).
- The app kept its pre-Laravel 11 skeleton (`app/Http/Kernel.php`, `app/Console/Kernel.php`);
  adopting the slim one is optional.

Still to do, in this order:

1. The frontend (frontend.md): Inertia 3, moving `inertiajs/inertia-laravel` and
   `@inertiajs/react` together; the MUI majors; and type-checking with `tsc` in CI. jQuery,
   Bootstrap 3 and select2 go as the last Blade pages using them move to Inertia.
2. Infrastructure, last: production MySQL 5.7 → 8.x, production PHP 8.5 (CI already runs it),
   deployment hardening, and swapping the local mysql and mailhog images.

Optional later: Sanctum, to modernise the door system's API token auth.

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

- `app/Entities/User.php` (~585 lines): extract concerns into traits/services along its natural
  seams — payment/subscription methods, role methods (already partly in `UserRoleTrait`), and the
  lifecycle transitions (`setPaymentWarning`, `setSuspended`, …) into a guarded `MembershipStatus`
  service or small state machine.
- `AccountController` ✅ done: registration, email confirmation, subscription changes and admin
  updates now have their own controllers (`MemberRegistrationController`,
  `EmailConfirmationController`, `AccountSubscriptionController`, `MemberAdminController` and
  others).

### Step 4 — ratchet quality

- PHPStan 2 (Larastan 3) runs at level 8, with level 6's missing-type checks (`missingType.*`)
  switched off: those are ~870 findings across most files, best turned on a file at a time.
  Relations carry generic `@return` types (`HasMany<TrainingRecord, $this>`), which Larastan 3
  needs to know their models. `phpstan-baseline.neon` holds 30 entries, all checked and found
  safe: mostly guarantees PHPStan can't see, such as a member always having a profile row, or a
  charge's member having been checked before billing. The 30th came with Carbon 3, which types
  `Carbon::create()` as possibly null: `PaymentController::index()` builds its date range from a
  fixed `Carbon::create(2009, 7, 1)`, a valid date that never returns null. New errors must be baselined
  *deliberately* (CI's `phpstan analyse` fails on un-baselined errors, so silencing one is a
  visible, reviewable change to the baseline file) — the aim is for the baseline to trend down over time, not a hard CI gate against growth.
- Add `declare(strict_types=1)` + native parameter/return types to files as they're touched.

## How to sequence against other work

The remaining upgrades (step 1) and the frontend migration (frontend.md) are independent tracks.
With the framework ladder done, the generation-1 cleanup (step 2) no longer risks double churn,
so it can go ahead whenever the code is touched.
