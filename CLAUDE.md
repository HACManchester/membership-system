# CLAUDE.md

Membership management system for Hackspace Manchester (adopted from Build Brighton's open-source
system — hence the `BB\` namespace). Laravel 7 (mid-upgrade), MySQL, GoCardless billing, mixed
Blade + Inertia/React frontend.

**Read `docs/README.md` first** — it indexes documentation on project health, domain subsystems,
architecture, frontend, testing, and a glossary of domain terms.

## Running commands

The local dev environment is Docker (Laravel Sail-style, `docker-compose.yml`).

- **All PHP commands run inside the container** — prefix with `docker compose exec laravel`:

  ```sh
  docker compose exec laravel php artisan migrate
  docker compose exec laravel composer install
  docker compose exec laravel vendor/bin/phpunit
  docker compose exec laravel vendor/bin/phpstan analyse
  ```

- **Node/yarn commands run directly on the host** (no prefix):

  ```sh
  yarn build      # production build (Vite, into public/build)
  yarn dev        # unminified build with source maps; `yarn watch` rebuilds on change
  yarn hot        # Vite dev server with hot reloading
  yarn lint       # ESLint
  yarn test       # Jest (two legacy .jsx suites so far)
  ```

Tests use SQLite in-memory (`phpunit.xml`) — no DB setup needed to run them.

## Where things live

- Models are in `app/Entities/` (not `app/Models/`).
- Routes: `routes/web.php` (everything) + `routes/api.php` (door-system export).
- Scheduled billing/status jobs: `app/Console/Kernel.php` — these are revenue-critical; read
  `docs/billing.md` before touching billing code.
- Legacy layers you'll encounter: `app/Repo/` (repositories), `app/Handlers/` (string-event
  handlers — still load-bearing for payments), `app/Validators/`, `app/Presenters/`. These are
  generation-1 patterns being retired — see `docs/architecture.md`.

## Conventions

- **Modernise what you touch, don't big-bang.** New/changed code uses: FormRequests that carry the
  authorization check in `authorize()` (calling a policy), policies for all authz, event classes +
  listeners for side effects, service objects for multi-step logic, Inertia + React + TypeScript
  for UI. The course/training subsystem (`CourseController`, `CoursePolicy`,
  `TrainCourseMemberRequest`, `CourseTrainingController` + their tests) is the reference
  implementation — match it.
- **Authorization goes through policies**, not route middleware alone and not ad-hoc role checks
  in controllers. Route middleware is defense-in-depth, never the sole gate.
- No new Blade pages or jQuery; converting a Blade page you're working on to Inertia is preferred
  (`docs/frontend.md`).
- Feature tests assert DB state / queued mail / fired events, not just status codes. Mock at the
  boundary (`GoCardlessHelper`), never internal services.
- Use `$request->validated()`, never `$request->all()`, for writes.
- Get the signed-in member with `BB\Support\CurrentUser::get()` (or `find()` where guests can reach
  the code), not `Auth::user()` / `$request->user()`, which can return the door system's `ApiKey`.
- **Docblocks only where they add information the signature can't express** — e.g. `@param` types
  for untyped parameters, `@var` on properties, `@property` blocks on entities, genuine prose.
  Don't write `@param`/`@return` tags that restate native type hints, `@return mixed`/`void`, or
  scaffold boilerplate descriptions; remove them from any method you touch.

## Gotchas

- **Money units are mixed**: `payments.amount` and `User.monthly_subscription` are **pounds**
  (payments as floats); `cash_balance` and `config/membership.php` are **pence**. Check units on
  every amount you handle.
- **Inertia props are sent to the browser in full**, rendered or not — shape props with dedicated
  Resources and include only what the page needs, especially for member data.
- The GoCardless webhook (`GoCardlessWebhookController`) is the only CSRF-exempt route and is
  signature-verified — keep it that way.
- The keyfob CSV export (`KeyFobCsvController`) is a de-facto contract with the door system —
  coordinate any output change with the acs team.
- `User.php` and `AccountController.php` are ~560-line god classes scheduled for splitting — avoid
  adding to them; extract instead.

===

<laravel-boost-guidelines>
=== .ai/hacmanchester rules ===

# This project's conventions

These override the generic Laravel Boost guidance above wherever they differ. The project's own
`CLAUDE.md` sections (above the Boost block) and `docs/` are the source of truth.

- **PHP runs in Docker.** Run Artisan, Composer, PHPUnit and PHPStan inside the container, e.g.
  `docker compose exec laravel php artisan route:list`, `docker compose exec laravel vendor/bin/phpunit`.
  Never run them with the host's `php`. Node and Yarn run on the host (`yarn dev`, `yarn build`).
- **The frontend builds with Vite** (`vite.config.mjs`, Blade's `@vite`), not Laravel Mix. Rebuild
  with `yarn build`, or run `yarn hot` for the dev server. It needs Node 20.19+.
- **Models live in `app/Entities/` under the `BB\` namespace**, not `App\Models`.
- **Test factories are legacy closure factories** (`laravel/legacy-factories`, `factory(User::class)`).
  Don't generate class-based factories for existing models without discussing it first.
- **Docblocks only where they add information** the signature can't express (generics, `@property`,
  untyped parameters, genuine prose). Don't add `@param`/`@return` that restate native types.
- **Deployment is self-hosted** (the `membership-deployment` repo, manual deploys), not Laravel Cloud.
- Billing, the GoCardless webhook and the keyfob CSV export are sensitive: read `docs/billing.md`
  and the relevant tests before changing them.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `yarn run build` or ask the user to run `yarn run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
