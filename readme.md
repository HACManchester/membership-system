# Hackspace Manchester Member System

To manage many aspects of member's access & use of the space and its equipment, we maintain our own Hackspace Manchester Membership System. This began as a fork of [ArthurGuy/BBMembershipSystem](https://github.com/ArthurGuy/BBMembershipSystem), but has since diverged to suit the unique needs of our space & equipment.

## Overview

### Features

- Member signup form which collects full name and address, emergency contact and profile photo.
- Direct Debit setup and payment collection through GoCardless
- Regular monthly direct debit payment runs for each user
- The ability for the user to edit all their details allowing for self management
- Various user statuses to cater for active members, members who have left or been banned as well as tracking founders and honorary members
- Handling of the induction/equipment training procedures and collection of payments.
- Tracking of who trains who
- Member grid to see who is a member
- The ability for members to cancel their subscription and leave
- Logs who has a physical key
- Manage member storage box assignments
- RFID door entry control and tracking
- Member credit system for paying for various services
- Member credit topup using direct debit payments and credit/debit card payments
- Member role system for managing delegated duties
- RFID access control for equipment and usage logging
- Auto billing for equipment usage
- Equipment/asset management

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
- Active
- Suspended - missed payment - DD is still active but the member doesn't have access to the workshop
- Leaving - The user has said they are leaving or they were in a payment warning state, member retains full access
- Left - Leaving users move here once their last payment expires.

## Third-party services

Running the membership system relies on a number of third-party services:

- (Email provider?)
- Discourse: Providing single-sign-on onto a Discourse application
- GoCardless: For managing subscription payments via direct debit
- Rollbar: For error monitoring & tracking
- Stripe: One-off payments (only available via the "Payment problem panel")
- Telegram: For notifying public & operational group chats about system events (new members, system notifications)
- ~~Amazon Web Services~~ (unused)
- ~~Paypal~~ (unused)
- ~~Pusher~~ (unused)
- ~~Slack~~ (unused)

These can be configured via environmental variables, or by setting up a `.env` file in the root of the project. See [`.env.example`](./.env.example) for reference.

### Storage

We don't use any third-party Cloud providers for storage of files.

All user-uploaded content is stored via the public storage disk at `storage/app/public`. There is a symlink pointing to this directory at `/public/storage`. This replicates the [public storage disk](https://laravel.com/docs/5.2/filesystem#the-public-disk) introduced in Laravel 5.2 (the next version above us).

## Development

The system is built on the PHP Laravel 5 framework so familiarity with that would help.

A .env file needs to be setup, please take a look at the example one for the options that are needed.
This file can be renamed by removing the .example from the end.

Composer needs to be available and the install command run to load the required assets.

The storage directory needs to be writable.

Some of the config options wont be needed.

AWS is used for file storage although a local option can be specified.

The system is built for a MySQL DB but a similar system will work

GoCardless for Direct Debit payments

MailGun for sending email - completely optional

The encryption key is essential and cannot be changed or lost once set

### Getting started

We have a Docker runtime adapted from Laravel Sail ([GitHub](https://github.com/laravel/sail)), adjusted to run PHP 7.2 and Node 6 (our live environment is running 4.8.2, but it's too much faff downgrading far enough to run that).

Prerequisites:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/install/) (comes pre-installed with Docker Desktop)

For Apple Silicon Macs, [OrbStack](https://github.com/orbstack/orbstack) might be worth exploring as a more efficient alternative to Docker for Mac.

1. Set up a .env file by copying `.env.example` to `.env`.

2. Install dependencies with:

   ```sh
   docker-compose run laravel composer install
   docker-compose run laravel yarn install
   ```

3. Build frontend assets with:

   ```sh
   docker-compose run laravel yarn run build
   ```

4. Start the local runtime with:

   ```sh
   docker-compose up -d
   ```

5. Provision the development database

   ```sh
   docker-compose run laravel php artisan migrate
   ```

6. Visit [https://localhost:8080](https://localhost:8080)

### Running console commands

If you need to run any console or `artisan` commands, you must do so within the Docker container.

You can open a shell directly in the container with:

```sh
docker-compose exec laravel bash
```

Or run your command from your host machine, prepended with:

```sh
docker-compose run laravel [command]
```

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

We currently host the membership system ourselves on our Bikeshed server, using Docker to encapsulate the relevant runtime.

The live Docker runtime is entirely separate to the local development runtime.

Production environment:

|                      |                                            |
| -------------------- | ------------------------------------------ |
| URL                  | <https://members.hacman.org.uk>            |
| Server               | `bikeshed.hacman.org.uk`                   |
| Runtime              | `/var/members/` (Docker Compose)           |
| Source code          | `/var/members/www/members`                 |
| Server configuration | `/var/members/config/vhosts`               |
| Crontab              | `/etc/cron.daily/membership-daily-updates` |

### Deployment process

We manually deploy code changes only at the moment, avoiding any post-deployment tasks until we gain confidence in the current hosting envronment's set up & resilience.

```sh
# On Bikeshed

cd /var/members/www/members
git pull origin master
```

If we need access to the PHP runtime, we can open a shell with:

```sh
# On Bikeshed

cd /var/members/
sudo docker-compose exec webserver bash
```

### Database

Database access information is kept private at the moment.

Ideally, database changes should be made via [Laravel Migrations](https://laravel.com/docs/5.1/migrations).

If you believe any changes need making directly to the database, please contact the membership comittee.

### Frontend assets

The site uses Bootstrap and SCSS files which can be transpiled to CSS.
However due to issues with transpiling that we haven't fixed, there's CSS hotfixes here and there.
This isn't dangerous or a risk, but would be nicer if it could be integrated with the SCSS.

### Routes

`app/Http/routes.php` is the starting point. Note the middlewares which do things like make
some things member only.

### Running jobs

Get into the docker container (above) and you can then run `php artisan bb:check-memberships` to run the `check-memberships` job, or any of the other jobs.
