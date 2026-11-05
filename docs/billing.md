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
| `payment_day` | Day of month to bill (values > 28 are clamped to 1 — the `paymentDay()` mutator in `HasSubscription`) |
| `monthly_subscription` | Chosen amount **in pence** (integer, e.g. 2250 for £22.50), passed to GoCardless as it is. Forms take pounds and convert once |
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
(**in pence**, integers), `status`, `paid_at`.

Statuses (`BB\Support\PaymentStatus`, cast on the model): `pending`, `pending_submission`
(normalised to `pending` on creation), `paid`, `cancelled`, `withdrawn`, `failed` (written by the
failure paths) and `error`. A GoCardless payment's own status can also be recorded as it stands
(e.g. `pending_customer_approval`, `charged_back`), so every GoCardless payment status is a case.
Statuses from GoCardless go through `PaymentStatus::fromGoCardless()`: one GoCardless adds later is
reported and recorded as `pending` (or `failed` on the failure path) rather than stopping the
billing run. `payments.status` was widened to 40 characters in November 2026; before that,
`pending_customer_approval` was stored cut short as `pending_customer_app`.

### SubscriptionCharge (`app/Entities/SubscriptionCharge.php`)

One row per member per billing month — "this member owes this month's subscription". Fields:
`user_id`, `charge_date`, `payment_date`, `amount` (**in pence**, like `monthly_subscription`), `status`
(`pending` → `due` → `processing` → `paid`, or `cancelled`; the `SubscriptionChargeStatus` enum).

**Mental model:** a `SubscriptionCharge` is the *invoice*; a `Payment` is the *money movement*
attempting to settle it; `Payment.reference = SubscriptionCharge.id` links them.

> **Don't trust `created_at` on `subscription_charge` on production.** Charges there have been
> observed with a `created_at` matching the moment a webhook marked them paid — a batch of
> confirmations delivered in one request left a group of charges all "created" in the same second,
> long after they were really raised. The likely cause is the age of the table: created in 2015
> with non-nullable timestamps, `created_at` would have been the first `TIMESTAMP` column and
> picked up MySQL's implicit `ON UPDATE CURRENT_TIMESTAMP`, so every write rewrites it. A
> freshly-migrated database does **not** reproduce this (`created_at timestamp NULL DEFAULT NULL`),
> so it can only be confirmed on production with `SHOW CREATE TABLE subscription_charge` — do that
> before building any conclusion on those timestamps. Either way, prefer `charge_date` (a plain
> `date`, written once) for when a charge is *for*, and the Payment's `paid_at` for when the money
> actually moved.

### Pricing (`config/membership.php`)

Tiered amounts (low income / standard / supporter), stored in pence in config, as
`monthly_subscription` is. `MembershipPayments::formatPrice()` formats pence for display.

> **Pence since November 2026.** Every amount the billing code handles is integer pence:
> `monthly_subscription`, `subscription_charge.amount`, `payments.amount`/`fee`/`amount_minus_fee`,
> `cash_balance` and the config. Forms take pounds and convert once, and amounts go to and come
> from GoCardless (which uses pence) unconverted. The two migrations that did this ship in one
> deploy:
>
> - `store_subscription_amounts_in_pence` multiplies `monthly_subscription` and
>   `subscription_charge.amount` by 100 in place (the columns were already integers). It first
>   checks every value is from 0 to 5000 pounds.
> - `store_payment_amounts_in_pence` converts the three `payments` columns from doubles of pounds
>   to integer pence, rounding away float noise (19.99 is stored as 1998.9999…). It first checks
>   every value is from 0 to 1000 pounds.
>
> Each stops without changing anything if its check fails. That also stops a second run: the
> largest subscription in production is £1000 and the largest payment £100, which the first run
> turns into 100000 and 10000, far above the limits. Back up the database first and deploy
> outside the nightly billing window (roughly 01:00–06:30), so nothing is charged or recorded
> mid-change. Reports that read these columns directly, such as Redash dashboards, need
> dividing by 100.
>
> The migrations run one after another, and the new code expects pence as soon as it's deployed.
> If one stops partway, the site would bill unconverted amounts as pence: a £22 member would be
> charged 22p. So before deploying, run these read-only checks on production; each must return 0:
>
> ```sql
> SELECT COUNT(*) FROM users WHERE hash IS NULL OR display_name IS NULL;
> SELECT COUNT(*) FROM users WHERE monthly_subscription < 0 OR monthly_subscription > 5000;
> SELECT COUNT(*) FROM subscription_charge WHERE amount < 0 OR amount > 5000;
> SELECT COUNT(*) FROM payments
>  WHERE amount < 0 OR amount > 1000 OR fee < 0 OR fee > 1000
>     OR amount_minus_fee < 0 OR amount_minus_fee > 1000;
> ```
>
> If any migration fails, don't bring the site back up: roll the code back first.
>
> Still in pounds: `gifts.credit` (a float, converted to pence when a gift is redeemed) and
> equipment `access_fee` (whole pounds, shown but not charged by the billing code). Equipment
> `usage_cost` is stored in pence, though its accessor reads and writes pounds.

---

## 2. The scheduled engine (`app/Console/Kernel.php`)

| Command | Does |
| --- | --- |
| `CreateTodaysSubCharges` | Creates `SubscriptionCharge` rows **7 days ahead**: for each billable active user whose `payment_day` matches (today + 7), create a `pending` charge (`MemberSubscriptionCharges::createSubscriptionCharges`) |
| `BillMembers` | (a) `makeChargesDue()`: flip `pending` charges whose `charge_date` has arrived to `due`; (b) `billMembers()`: for each `due` charge with **no existing payment for that charge**, submit a GoCardless bill and record a `pending` Payment. Two kinds of charge never get billed: those belonging to a member who can't be billed at all (inactive, or no payment method — these are **cancelled** and reported), and direct debits more than `membership.billing.max_charge_age_days` past their charge date (**left alone** and reported for review) |
| `CheckMembershipStatus` | Runs five processes: `RecoverMemberships` (re-activate anyone whose recent payment covers them), `CheckPaymentWarnings` (warning expired → suspend), `CheckSuspendedUsers` (suspended > 30 days → left), `CheckLeavingUsers` (leaving + expired → left), `CheckExpiredActiveUsers` (active but expired > 7 days, or no expiry at all → payment-warning — the backstop for failures that never produced a webhook) |
| `CheckForPossibleDuplicates` | Flags duplicate-looking pending payments for manual review (Telegram notification); no auto-dedup |

These run daily on the Laravel scheduler — see `app/Console/Kernel.php` for the exact times and
`docs/operations.md` for the operational picture.

**Billing a charge twice can't collect twice.** A charge's Payment row is written only after
GoCardless has created the payment, so a failure in between leaves the charge looking unbilled and
the next run bills it again. Each bill for a charge therefore sends an `Idempotency-Key`
(`SubscriptionCharge::goCardlessIdempotencyKey()`: charge ID, member, charge date and mandate).
GoCardless makes at most one payment per key and answers a repeat with the existing payment,
which is recorded instead. One-off payments (top-ups, fees) keep the SDK's random per-call key.

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
| `mandates.cancelled` | Find user by `mandate_id` → `MembershipStatus::cancelSubscription()` (clears mandate/method, status → `leaving`, cancels outstanding charges) |
| `subscriptions.cancelled` | Legacy fixed-DD cleanup; clears `subscription_id` |
| `subscriptions.payment_created` | Legacy fixed-DD: records the payment |

### Event chains (legacy string events — generation 1, see architecture.md)

**Success:** `payment.paid` → `PaymentEventHandler::onPaid()` → if `reason=subscription`,
`SubscriptionChargeRepository::markChargeAsPaid(reference)` → charge `paid` → fires
`SubscriptionChargePaid` → `ExtendMembership` listener →
`MembershipStatus::extend(user, payment_date + 1 month)` → status `active`, `subscription_expires` pushed
out, `suspended_at` cleared.

**Failure:** `payment.cancelled` → `PaymentEventHandler::onCancel()` →
`SubscriptionChargeRepository::paymentFailed(reference)` → charge `cancelled` → fires
`sub-charge.payment-failed` → `SubChargeEventHandler::onPaymentFailure()` → *only if no other live
payment exists for that charge* → `MembershipStatus::setPaymentWarning()` → status `payment-warning` (access
kept), `subscription_expires = now + grace period`.

---

## 4. Lifecycle narrative

**Signup / payment setup.** Member sets `monthly_subscription`, starts the GoCardless redirect
flow (`SubscriptionController@create` → `GoCardlessHelper::newPreAuthUrl`; `gocardless_setup_id`
stored). On return (`@store`), the mandate is confirmed and stored, `payment_method` becomes
`gocardless-variable`, `payment_day` set to today's day, and
`MembershipStatus::activate()` activates the account — if membership is expired and no
charge is outstanding, it creates a charge **and bills it immediately**
(`SubscriptionChargeRepository::createChargeAndBillDD`).

**Steady state.** 7 days before each member's `payment_day`, a `pending` charge appears. On the
day: charge → `due` → GoCardless bill submitted → `pending` Payment recorded. Over the next ~2–3
working days webhooks walk it `submitted` → `confirmed`, the charge flips to `paid`, and
`subscription_expires` extends one month from the payment date.

**Failure.** A `payments.failed` webhook cancels the charge and puts the member in
`payment-warning` with a grace period (currently 10 days). If the payment can't even be *created*
at GoCardless (e.g. an unusable mandate), no webhook will ever arrive, so the billing run itself
puts the member through the same path — and `CheckExpiredActiveUsers` backstops anyone who slips
through by catching active members whose paid-up date lapsed anyway. They keep access during the
warning.
If they pay (e.g. a manual one-off via `GoCardlessPaymentController`),
`PaymentEventHandler::updateSubPayment()` links the payment to the oldest unpaid charge, and the
daily `RecoverMemberships` re-activates them. If the warning expires: `suspended` (access revoked
— the keyfob export only includes active users). After 30 days suspended: `left`.

**Leaving.** Member cancels (`SubscriptionController@destroy`): mandate cancelled at GoCardless,
then `MembershipStatus::cancelSubscription()` clears the payment fields, sets status `leaving`
and **cancels the outstanding charges**. The daily check flips `leaving` → `left` once
`subscription_expires` passes, or immediately if there is no expiry date to wait for. A mandate
cancelled *at the bank* arrives as a `mandates.cancelled` webhook and goes through the same
method — it must, or the charges it leaves behind become the backlog described below.

**The rejoin backlog (fixed, worth understanding).** Cancelling used to leave outstanding charges
in place. Nothing could collect them — `billMembers()` filtered out members with no payment method
without a word — so they sat at `due` while the nightly run added another every month for as long
as the member stayed `active`. When the member rejoined, the new mandate made the whole backlog
billable at once and it went out in a single run: a year of backdated subscription payments in one
night. Three things now prevent it: cancellation takes the charges with it, `billMembers()`
cancels-and-reports charges it can't collect instead of dropping them, and it refuses to collect
anything older than the configured age. `bb:audit-sub-charges` finds and clears any historic
backlog.

**Rejoining.** A returning member redoes the mandate setup; `MembershipStatus::activate()` reactivates
and bills immediately. History (payments, charges) is retained.

**Special cases.** `honorary` status members and members with an unexpired gift (`gift_expires`
in the future) are excluded from billing (`getBillableActive()`, via the `notSpecialCase` scope).
`online_only` accounts are *not* exempt: an active online-only account is charged like any other.
Cash balance (`cash_balance`, managed by `app/Services/Credit.php`, topped up via
`CashPaymentController` by staff) is a separate ledger derived entirely from Payment rows
(`reason=balance` adds, `source=balance` subtracts).

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

1. **Integer pence everywhere.** Billing is all pence now; `gifts.credit` is the last amount
   in pounds the money path reads.
2. **Typed events.** Convert the string-event handler chain (`PaymentEventHandler`,
   `SubChargeEventHandler`) to event classes with listeners — the money path deserves static
   analysis visibility.
3. **Webhook test fidelity.** The endpoint now has feature tests (`GoCardlessWebhookTest`) covering
   each event type and signature verification, but they use hand-crafted payloads; capturing a few
   real payloads as fixtures would close the format-fidelity gap (see testing.md).
4. **Configurable grace period.** The payment-warning grace period is hardcoded in
   `MembershipStatus::setPaymentWarning()`; it should come from config.
5. **A guarded state machine.** The `User.status` transitions live in the `MembershipStatus`
   service, but each still sets its status unconditionally; refusing transitions the diagram above
   doesn't allow would make it enforceable code.
