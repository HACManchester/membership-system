# Hackspace Manchester Member System

To manage many aspects of member's access & use of the space and its equipment, we maintain our own Hackspace Manchester Membership System. This began as a fork of [ArthurGuy/BBMembershipSystem](https://github.com/ArthurGuy/BBMembershipSystem), but has since diverged to suit the unique needs of our space & equipment.

The system is built on PHP/Laravel (currently Laravel 7, being upgraded incrementally) with a
frontend that is migrating from Blade/jQuery to Inertia.js + React + TypeScript. See the
[`docs/`](./docs/README.md) folder for contributor documentation: project health, domain
subsystems, architecture, frontend, testing, and a glossary of domain terms.

## Overview

### Features

- Member signup (full and online-only tiers) collecting profile details and a profile photo
- Direct Debit setup and payment collection through GoCardless
- Automated monthly billing runs and membership status transitions (warning/suspension/recovery)
- Self-service member detail editing
- Various user statuses to cater for active members, members who have left or been banned, as well as honorary members
- Equipment catalogue with induction/training tracking — including the newer course-based training system with trainer sign-off
- Tracking of who trains who, plus maintainer groups and equipment areas
- Member directory and trainer leaderboard
- The ability for members to cancel their subscription and leave
- Key fob and door access code management, exported to the door entry system
- Member storage box assignment and claiming
- Member credit/balance system
- Member role system for managing delegated duties
- Discourse forum sync (membership status and SSO)
- Admin and finance tooling (payment overview, balances, stats)

### Account tiers

There are two primary tiers of accounts:

- Online-only accounts: Limited access to the membership system & space, but enables participation in our community
- Members: Those with a regular subscription for access to the space & its equipment.

The sign-up flows for each is slightly different:

#### Online-only

Sign up -> get online only welcome email -> confirm email

Online only is stored in the DB as such. Dummy data is inserted so as not to break db constraints, but this is caught when updating your details. This means users can go from online only to a full member. They can't go back to online only at the moment - their membership will just go to being expired, but will still work for SSO.

#### As a member

Sign up -> setup payment -> get welcome email

### Member Statuses

There are a variety of member statuses which are used for various scenarios.

- Setting Up - just signed up, no subscription setup, no access to space
- Active - paid member with full access to the space
- Payment Warning - payment failed but member retains access during 10-day grace period
- Suspended - grace period expired, no space access but can still recover
- Leaving - member voluntarily canceling but retains access until subscription expires
- Left - former member who has completed leaving process
- Honorary - special exempt status

### Subscription & Payment Process

The membership system handles subscription payments and automatic state transitions:

#### Payment Success Flow

1. Member sets up Direct Debit via GoCardless
2. Monthly payments taken automatically on their chosen day
3. Each successful payment extends membership by 1 month
4. Member remains in `Active` status with full space access

#### Payment Failure & Recovery

1. **Payment fails** → Member enters `Payment Warning` status
   - 10-day grace period begins
   - Member retains full space access
   - Daily reminder emails sent
   - Can recover by making payment within grace period

2. **Grace period expires** → Member becomes `Suspended`
   - Space access removed
   - 30-day recovery window
   - Can still reactivate by making payment

3. **30 days suspended** → Member marked as `Left`
   - Must set up new subscription to rejoin
   - No explicit "rejoin" process - just set up payment again

#### Voluntary Leaving

1. Member cancels subscription → enters `Leaving` status
2. Retains access until current paid period expires
3. Automatically transitions to `Left` when subscription expires

#### Daily Automated Processes

- `RecoverMemberships` - Checks for new payments and reactivates members
- `CheckPaymentWarnings` - Moves expired warnings to suspended
- `CheckSuspendedUsers` - Marks 30-day suspended members as left
- `CheckLeavingUsers` - Transitions expired leaving members to left

## Third-party services

Running the membership system relies on a number of third-party services:

- (Email provider?)
- Discourse: Providing single-sign-on onto a Discourse application
- GoCardless: For managing subscription payments via direct debit
- Sentry: For error monitoring & tracking
- Telegram: For notifying public & operational group chats about system events (new members, system notifications)

These can be configured via environmental variables, or by setting up a `.env` file in the root of the project. See [`.env.example`](./.env.example) for reference.

### Storage

We don't use any third-party Cloud providers for storage of files.

All user-uploaded content is stored via the [public storage disk](https://laravel.com/docs/7.x/filesystem#the-public-disk) at `storage/app/public`. There is a symlink pointing to this directory at `/public/storage`.

## Development

The system is built on the PHP Laravel framework (currently Laravel 7, with an incremental upgrade
underway — see [`docs/architecture.md`](./docs/architecture.md)), backed by MySQL.

A `.env` file needs to be set up — copy `.env.example` and adjust. Notes:

- The encryption key (`APP_KEY`) is essential and cannot be changed or lost once set.
- GoCardless credentials are needed for Direct Debit payment flows (sandbox keys work locally).
- Mail, Discourse, Sentry and Telegram integrations are optional for local development.
- User-uploaded files are stored locally on the public storage disk (no cloud storage is used).

### Getting started

We have a Docker runtime adapted from Laravel Sail ([GitHub](https://github.com/laravel/sail)),
currently running PHP 7.2 and Node 16 to match the live environment.

Prerequisites:

- [Docker](https://www.docker.com/) with Docker Compose
- [Node.js](https://nodejs.org/) + [Yarn](https://yarnpkg.com/) on your host machine (frontend tooling runs on the host, not in the container)

For Apple Silicon Macs, [OrbStack](https://github.com/orbstack/orbstack) might be worth exploring as a more efficient alternative to Docker for Mac.

1. Set up a .env file by copying `.env.example` to `.env`.

2. Install PHP dependencies (in the container) and frontend dependencies (on the host):

   ```sh
   docker compose run laravel composer install
   yarn install
   ```

3. Build frontend assets (on the host):

   ```sh
   yarn build
   ```

4. Start the local runtime with:

   ```sh
   docker compose up -d
   ```

5. Provision the development database

   ```sh
   docker compose exec laravel php artisan migrate
   ```

6. Visit [http://localhost:8080](http://localhost:8080) (or whatever `APP_PORT` you configured)

### Running console commands

**PHP/artisan/composer commands run inside the Docker container; Node/yarn commands run on the
host.**

You can open a shell directly in the container with:

```sh
docker compose exec laravel bash
```

Or run a single command from your host machine, prepended with:

```sh
docker compose exec laravel [command]
```

### Running tests

The test suite uses an in-memory SQLite database, so no extra setup is needed:

```sh
docker compose exec laravel vendor/bin/phpunit
docker compose exec laravel vendor/bin/phpstan analyse
yarn lint
```

See [`docs/testing.md`](./docs/testing.md) for the test suite layout, conventions, and where new
tests are most valuable.

### Troubleshooting

If you have any issues, see if the docker logs have any useful information:

```sh
docker-compose logs laravel
```

Or the Laravel log file at [storage/logs/laravel-DATE.log](./storage/logs):

```sh
tail -n 20 storage/logs/laravel-$(date -I).log
```

## Deployment & hosting environments

The live system at <https://members.hacman.org.uk> is self-hosted on Hackspace Manchester
infrastructure, using Docker Compose to encapsulate the runtime. The live runtime is entirely
separate to the local development runtime.

See [`docs/operations.md`](./docs/operations.md) for what the system does automatically when
deployed (scheduled jobs, observability, integrations). For deployment access or server details,
contact the membership committee.

### Database

Database access information is kept private.

Database changes should be made via [Laravel Migrations](https://laravel.com/docs/7.x/migrations).
If you believe any changes need making directly to the database, please contact the membership
committee.

### Frontend assets

The frontend is mid-migration: legacy pages use Blade templates with LESS/Bootstrap 3, while newer
pages (courses, training, newsletter) use Inertia.js + React + TypeScript with Material-UI. Both
are built by Laravel Mix (`webpack.mix.js`). New pages should use the modern stack — see
[`docs/frontend.md`](./docs/frontend.md) for the migration plan.

### Routes

[`routes/web.php`](./routes/web.php) is the starting point (plus [`routes/api.php`](./routes/api.php)
for the door-system export). Note the middlewares which do things like make some things member
only — though authorization decisions belong in policies, not middleware alone (see
[`docs/architecture.md`](./docs/architecture.md)).

### Running jobs

Get into the docker container (above) and you can then run `php artisan bb:check-memberships` to run the `check-memberships` job, or any of the other jobs. The scheduled jobs are defined in `app/Console/Kernel.php`.
