# Operational Overview

What the system does automatically in a deployed environment, and how to observe and operate it.
This documents the application's operational behaviour — hosting specifics (servers, deployment
access) are not covered here; contact the membership committee if you need them.

## The scheduled jobs

The application relies on the Laravel scheduler (a standard `php artisan schedule:run` cron
invocation). The schedule is defined in `app/Console/Kernel.php`; the daily jobs are the heart of
the system:

| Command | Purpose |
| --- | --- |
| `CreateTodaysSubCharges` | Creates subscription charges 7 days ahead of each member's payment day |
| `BillMembers` | Marks arrived charges as due and submits them to GoCardless |
| `CheckMembershipStatus` | Runs the membership state transitions: recovery, payment-warning expiry → suspension, 30-day suspension → left, leaving → left |
| `CheckForPossibleDuplicates` | Flags duplicate-looking pending payments for manual review |

If the scheduler doesn't run, **nothing bills and no membership states transition** — any
deployment of this system needs the scheduler wired up and monitored. See billing.md for what each
job does in detail.

The billing commands post a summary to the configured Telegram chat when they act, which doubles
as a daily heartbeat.

## Running jobs manually

All artisan commands run inside the application container:

```sh
docker compose exec laravel php artisan bb:check-memberships   # or any other command
docker compose exec laravel php artisan schedule:run -v        # shows which tasks are due
docker compose exec laravel php artisan tinker                 # fires observers/events properly,
                                                               # unlike raw SQL edits
```

Prefer artisan/tinker over direct database edits — the `User` lifecycle relies on observers and
events firing (see domain-subsystems.md, cross-cutting section).

## Observability

- **Sentry** — server-side exceptions (`app/Exceptions/Handler.php`) and browser-side errors
  (initialised in `resources/views/app.blade.php`).
- **Telegram** — operational notifications (new members, lifecycle transitions, billing runs) and
  exception alerts. Exception alerts are rate-limited so a repeating error doesn't flood the chat:
  first occurrence always sends, then only at order-of-magnitude thresholds.
- **Log files** — `storage/logs/laravel-<date>.log`; admins can read them in-app at `/logs`
  (rap2hpoutre/laravel-log-viewer).

All three no-op gracefully when unconfigured, so local development needs none of them.

## Queues and mail

Jobs like `DiscourseSync` are queueable. With `QUEUE_CONNECTION=sync` (the simplest setup) they
run inline in the triggering request — which means a slow or unreachable Discourse makes the
triggering action (login, profile save) slow. If that becomes a problem, the fix is a real queue
driver plus a worker process.

Mail is sent through whatever provider `.env` configures (SMTP/Mailgun supported); locally,
Mailhog in the compose stack catches everything.

## External integration points

| Integration | Direction | Notes |
| --- | --- | --- |
| GoCardless | Outbound API + inbound webhook | The webhook is signature-verified and is the only CSRF-exempt route. The GoCardless dashboard can re-send webhook events; redelivery is handled by looking payments up by `source_id`. |
| Door access system | Inbound pull | The door controller periodically pulls the keyfob export (`/api/keyfobs/csv`, token-authenticated). The export format is a de-facto contract — coordinate changes with the acs team. |
| Discourse | Outbound (queued job) | Signed SSO sync on login and membership status changes. |
| Telegram / Sentry | Outbound | Observability, above. |

## Deployment

The live system is self-hosted with Docker Compose, deployed from this repository's `master`
branch. Database changes go through Laravel migrations. For deployment access or environment
details, contact the membership committee.
