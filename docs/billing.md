# Membership Subscriptions & Billing

How member billing works: the data model, the full lifecycle, the state machines, the scheduled
jobs, and the GoCardless webhook handling. This is the most consequential subsystem in the
codebase — read this before touching anything under `app/Services/MemberSubscriptionCharges.php`,
`app/Repo/SubscriptionChargeRepository.php`, `app/Repo/PaymentRepository.php`, `app/Handlers/`,
or `GoCardlessWebhookController`.

---

## 1. Data model

### User (payment-related fields)

| Field | Meaning |
| --- | --- |
| `payment_method` | `gocardless-variable` (current), `gocardless` (legacy fixed subscription), `standing-order`, `cash`, … |
| `payment_day` | Day of month to bill (values > 28 are clamped to 1 — `User::setPaymentDayAttribute`) |
| `monthly_subscription` | Chosen amount **in pounds** (e.g. 22) — multiplied by 100 at the GoCardless boundary |
| `mandate_id` | GoCardless mandate (variable DD — the current system) |
| `subscription_id` | GoCardless subscription (legacy fixed DD only) |
| `gocardless_setup_id` | Temporary redirect-flow ID during mandate setup |
| `subscription_expires` | Paid-up-until date; drives status transitions |
| `status` | `setting-up` / `active` / `payment-warning` / `suspended` / `leaving` / `left` / `honorary` |
| `cash_balance` | Balance credit **in pence** (integer) |
| `suspended_at` | When suspension started (drives the 30-day → `left` rule) |

### Payment (`app/Entities/Payment.php`)

One row per money movement. Key fields: `source` (`gocardless-variable`, `cash`, `balance`, …),
`source_id` (the GoCardless payment ID — how webhooks find us), `reference` (**the
SubscriptionCharge ID** when the payment pays a subscription charge — this is the join between the
two tables), `reason` (`subscription`, `balance`, `induction`, …), `amount`/`fee`/`amount_minus_fee`
(in pounds — see the money-units note in project-health.md), `status`, `paid_at`.

Statuses: `pending`, `pending_submission` (normalised to `pending` on creation), `paid`,
`cancelled`, `withdrawn` (+ `failed` written by the webhook failure path).

### SubscriptionCharge (`app/Entities/SubscriptionCharge.php`)

One row per member per billing month — "this member owes this month's subscription". Fields:
`user_id`, `charge_date`, `payment_date`, `amount`, `status`
(`pending` → `due` → `processing` → `paid`, or `cancelled`).

**Mental model:** a `SubscriptionCharge` is the *invoice*; a `Payment` is the *money movement*
attempting to settle it; `Payment.reference = SubscriptionCharge.id` links them.

### Pricing (`config/membership.php`)

Tiered amounts (low income / standard / supporter), stored in pence in config while
`monthly_subscription` is in pounds — one of the unit inconsistencies tracked in
project-health.md.

---

## 2. The scheduled engine (`app/Console/Kernel.php`)

| Command | Does |
| --- | --- |
| `CreateTodaysSubCharges` | Creates `SubscriptionCharge` rows **7 days ahead**: for each billable active user whose `payment_day` matches (today + 7), create a `pending` charge (`MemberSubscriptionCharges::createSubscriptionCharges`) |
| `BillMembers` | (a) `makeChargesDue()`: flip `pending` charges whose `charge_date` has arrived to `due`; (b) `billMembers()`: for each `due` charge with **no existing payment for that charge**, submit a GoCardless bill and record a `pending` Payment |
| `CheckMembershipStatus` | Runs four processes: `RecoverMemberships` (re-activate anyone whose recent payment covers them), `CheckPaymentWarnings` (warning expired → suspend), `CheckSuspendedUsers` (suspended > 30 days → left), `CheckLeavingUsers` (leaving + expired → left) |
| `CheckForPossibleDuplicates` | Flags duplicate-looking pending payments for manual review (Telegram notification); no auto-dedup |

These run daily on the Laravel scheduler — see `app/Console/Kernel.php` for the exact times and
`docs/operations.md` for the operational picture.

---

## 3. Webhooks (`GoCardlessWebhookController`)

The only CSRF-exempt route, and signature-verified — a request that fails verification is
rejected.

| Event | Handler effect |
| --- | --- |
| `payments.created` | Log only (we created it ourselves) |
| `payments.submitted` | Payment (found by `source_id`) → `pending` |
| `payments.confirmed` | Payment → `paid`, `paid_at=now()`; fires `payment.paid` |
| `payments.paid_out` | Ignored |
| `payments.failed` / `payments.cancelled` | Re-fetch from GC API; Payment → `failed`/`cancelled`; fires `payment.cancelled` |
| `mandates.cancelled` | Find user by `mandate_id` → `cancelSubscription()` (clears mandate/method, status → `leaving`) |
| `subscriptions.cancelled` | Legacy fixed-DD cleanup; clears `subscription_id` |
| `subscriptions.payment_created` | Legacy fixed-DD: records the payment |

### Event chains (legacy string events — generation 1, see architecture.md)

**Success:** `payment.paid` → `PaymentEventHandler::onPaid()` → if `reason=subscription`,
`SubscriptionChargeRepository::markChargeAsPaid(reference)` → charge `paid` → fires
`SubscriptionChargePaid` → `ExtendMembership` listener →
`user->extendMembership(payment_date + 1 month)` → status `active`, `subscription_expires` pushed
out, `suspended_at` cleared.

**Failure:** `payment.cancelled` → `PaymentEventHandler::onCancel()` →
`SubscriptionChargeRepository::paymentFailed(reference)` → charge `cancelled` → fires
`sub-charge.payment-failed` → `SubChargeEventHandler::onPaymentFailure()` → *only if no other live
payment exists for that charge* → `user->setPaymentWarning()` → status `payment-warning` (access
kept), `subscription_expires = now + grace period`.

---

## 4. Lifecycle narrative

**Signup / payment setup.** Member sets `monthly_subscription`, starts the GoCardless redirect
flow (`SubscriptionController@create` → `GoCardlessHelper::newPreAuthUrl`; `gocardless_setup_id`
stored). On return (`@store`), the mandate is confirmed and stored, `payment_method` becomes
`gocardless-variable`, `payment_day` set to today's day, and
`UserRepository::ensureMembershipActive()` activates the account — if membership is expired and no
charge is outstanding, it creates a charge **and bills it immediately**
(`SubscriptionChargeRepository::createChargeAndBillDD`).

**Steady state.** 7 days before each member's `payment_day`, a `pending` charge appears. On the
day: charge → `due` → GoCardless bill submitted → `pending` Payment recorded. Over the next ~2–3
working days webhooks walk it `submitted` → `confirmed`, the charge flips to `paid`, and
`subscription_expires` extends one month from the payment date.

**Failure.** A `payments.failed` webhook cancels the charge and puts the member in
`payment-warning` with a grace period (currently 10 days). They keep access during the warning.
If they pay (e.g. a manual one-off via `GoCardlessPaymentController`),
`PaymentEventHandler::updateSubPayment()` links the payment to the oldest unpaid charge, and the
daily `RecoverMemberships` re-activates them. If the warning expires: `suspended` (access revoked
— the keyfob export only includes active users). After 30 days suspended: `left`.

**Leaving.** Member cancels (`SubscriptionController@destroy`): mandate cancelled at GoCardless,
payment fields cleared, status `leaving`, outstanding charges cancelled. The daily check flips
`leaving` → `left` once `subscription_expires` passes. A mandate cancelled *at the bank* arrives
as a `mandates.cancelled` webhook with the same effect.

**Rejoining.** A returning member redoes the mandate setup; `ensureMembershipActive()` reactivates
and bills immediately. History (payments, charges) is retained.

**Special cases.** `honorary` status members and `online_only` accounts are excluded from billing
(`getBillableActive()` scope). Cash balance (`cash_balance`, managed by `app/Services/Credit.php`,
topped up via `CashPaymentController` by staff) is a separate ledger derived entirely from Payment
rows (`reason=balance` adds, `source=balance` subtracts).

---

## 5. State machines (quick reference)

```text
SubscriptionCharge:  pending ──(charge_date arrives)──► due ──(billed)──► [Payment pending]
                     ──(payments.submitted)──► processing ──(payments.confirmed)──► paid
                     any ──(payment failed, no other live payment / member leaves)──► cancelled

Payment:             pending ──► paid (webhook confirmed)
                     pending ──► failed | cancelled (webhook failed/cancelled)

User.status:         setting-up ──► active ──► payment-warning ──► suspended ──► left
                                     ▲   ▲          │ (pays: RecoverMemberships)
                                     │   └──────────┘
                                     └── leaving ──► left          (banned: left + banned flag)
```

---

## 6. Improvement areas

Engineering work this subsystem would benefit from (see architecture.md for sequencing):

1. **Integer pence everywhere.** Amounts currently mix pounds and pence across tables and config
   (see project-health.md) — migrate to integer pence with a `Money`-style accessor.
2. **Typed events.** Convert the string-event handler chain (`PaymentEventHandler`,
   `SubChargeEventHandler`) to event classes with listeners — the money path deserves static
   analysis visibility.
3. **Webhook test fidelity.** The endpoint now has feature tests (`GoCardlessWebhookTest`) covering
   each event type and signature verification, but they use hand-crafted payloads; capturing a few
   real payloads as fixtures would close the format-fidelity gap (see testing.md).
4. **Configurable grace period.** The payment-warning grace period is hardcoded in
   `User::setPaymentWarning()`; it should come from config.
5. **A guarded state machine.** The `User.status` transitions are implemented as independent
   setters; folding them into a single `MembershipStatus` service would make the diagram above
   enforceable code.
