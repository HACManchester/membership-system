# Glossary

Domain terms as used in this codebase. Several are confusable — the *Induction* entity alone has
three meanings in casual speech. When writing code or docs, prefer the precise terms below.

## Membership & lifecycle

| Term | Meaning |
| --- | --- |
| **Member** | Loosely used. Precisely: an account with `status='active'`. |
| **Online-only member** | Account with `online_only=true` — forum/community access without physical access or billing. Cannot register fobs/codes. |
| **Honorary member** | `status='honorary'` — active without billing; excluded from charge creation. |
| **Setting-up** | `status='setting-up'` — registered but not yet activated (email confirmation / payment setup pending). The only status that is hard-deleted on account deletion. |
| **Payment warning** | `status='payment-warning'` — a subscription payment failed; member keeps access during a grace period. |
| **Suspended** | `status='suspended'` — grace period expired; `active=false`, door access revoked. After 30 days becomes `left`. |
| **Leaving / Left** | `leaving` = cancellation in progress (until paid-up period expires); `left` = no longer a member. Banned members are also `left`, distinguished by the `banned` flag. |
| **Trusted** | `User.trusted` flag — vouched-for member; checked by the `IsTrusted` middleware for certain features. Audited field. |
| **Key holder** | `User.key_holder` flag — may access the space outside open evenings. Audited field. Distinct from holding a key fob! |

## Training & equipment

| Term | Meaning |
| --- | --- |
| **General induction** | The one-time health-and-safety onboarding (code entered at `/general_induction`). Sets `User.induction_completed`. Prerequisite for fobs/access codes. *Not* related to the `Induction` entity. |
| **Induction (entity)** | `app/Entities/Induction.php` — one member's training record for one piece of equipment/course. Despite the name, it's a *training record*, including trainer status. |
| **Equipment induction** | The process of being trained on a specific machine, producing an `Induction` row with `trained` set. |
| **Course** | The modern grouping of training (`Course` ↔ many `Equipment`); replaces the legacy `induction_category` string-matching. An `Induction` row links to a course via `course_id`. |
| **Induction category** (legacy) | String key on `Equipment` matched against `Induction.key`. Being retired in favour of Courses; still used by the notification email tool. |
| **Sign-off request** | Course-flow step: member requests trainer sign-off (`sign_off_requested_at`, expires after 7 days). |
| **Trainer** | A member with `is_trainer=true` on their `Induction` for that equipment — may train others, gets a leaderboard entry, can email trainees. |
| **Maintainer** | Member of a `MaintainerGroup` linked to equipment — maintains the machine; gets management rights via policies. Not necessarily a trainer. |
| **Area coordinator** | Member attached to an `EquipmentArea` (e.g. woodshop) — coordinates an area; policy-level management rights over its equipment. |

## Billing

| Term | Meaning |
| --- | --- |
| **Subscription charge** | `SubscriptionCharge` — the monthly *invoice*: "this member owes this month". Statuses: pending → due → processing → paid / cancelled. |
| **Payment** | A money movement (GoCardless, cash, balance…). Links to the charge it settles via `Payment.reference = SubscriptionCharge.id`. |
| **Mandate** | GoCardless authorization to debit a member's bank account (`User.mandate_id`). The basis of **variable DD**, the current billing system. |
| **Variable DD vs fixed DD** | Variable (`gocardless-variable` + mandate): we create each month's bill ourselves — current system. Fixed (`gocardless` + `subscription_id`): GoCardless ran the subscription — legacy, being migrated away from (see the `/stats` DD-switch tracker). |
| **Payment day** | Day of month the member is billed (`payment_day`; >28 clamps to 1). Charges are created 7 days ahead. |
| **Balance / cash balance** | Member credit ledger in **pence** (`User.cash_balance`), derived from Payment rows (`reason='balance'` adds, `source='balance'` spends). Managed by `app/Services/Credit.php`. |
| **Snackspace** | Legacy payment reason from Build Brighton (honesty-shop purchases). Appears in old data; not an active feature. |
| **Grace period** | Days a member keeps access after a failed payment — distinct from GoCardless's own clearing window. |

## Physical access

| Term | Meaning |
| --- | --- |
| **Key fob** | RFID token; `KeyFob.key_id` is its serial. Registered after general induction. |
| **Access code** | A generated door PIN, stored as a `KeyFob` row with a `ff`-prefixed `key_id` — the prefix marks it as a code, not a fob. |
| **Door system / ACS** | The physical access-control system, run by the `acs` team. It *pulls* the keyfob export — the membership system does not push or receive door events. |
| **Marked lost** | Soft revocation of a fob (`lost=true, active=false`). |

## People & data

| Term | Meaning |
| --- | --- |
| **Display name** | `display_name` — the member's chosen name shown in the directory and UI. |
| **Announce name** | `announce_name` — the name exported to the door system / announcements. |
| **Suppress real name** | `suppress_real_name` flag — keep given/family name out of Discourse sync and other surfaces. |
| **Profile private** | `profile_private` flag — hide the profile from the member directory. |
| **Role** | A named group (`admin`, `finance`, `laser`…) granting permissions and a contact email. Seeded via migrations, assigned by admins. |

## Project & infrastructure

| Term | Meaning |
| --- | --- |
| **BB namespace** | `BB\` = Build Brighton — the hackspace whose open-source system this was adopted from. Kept to avoid churn. |
| **Generation 1/2/3** | Shorthand from architecture.md for the three eras of code patterns coexisting in the repo. |
| **The ladder** | The staged Laravel 7→8→9→10→11 upgrade path (architecture.md). |
