# This project's conventions

These override the generic Laravel Boost guidance above wherever they differ. The project's own
`CLAUDE.md` sections (above the Boost block) and `docs/` are the source of truth.

- **PHP runs in Docker.** Run Artisan, Composer, PHPUnit and PHPStan inside the container, e.g.
  `docker compose exec laravel php artisan route:list`, `docker compose exec laravel vendor/bin/phpunit`.
  Never run them with the host's `php`. Node and Yarn run on the host (`yarn dev`, `yarn build`).
- **The frontend builds with Vite** (`vite.config.mjs`, Blade's `@vite`), not Laravel Mix. Rebuild
  with `yarn build`, or run `yarn hot` for the dev server. It needs Node 20.19+.
- **Models live in `app/Entities/` under the `BB\` namespace**, not `App\Models`.
- **Test factories are class-based**, one per model in `database/factories/` (`Database\Factories\`).
  Entities opt in with `HasFactory` plus `#[UseFactory(XFactory::class)]`, since name guessing looks in
  `App\Models`. A new model needs a factory too: `ModelFactoriesTest` fails without one.
- **Docblocks only where they add information** the signature can't express (generics, `@property`,
  untyped parameters, genuine prose). Don't add `@param`/`@return` that restate native types.
- **Deployment is self-hosted** (the `membership-deployment` repo, manual deploys), not Laravel Cloud.
- Billing, the GoCardless webhook and the keyfob CSV export are sensitive: read `docs/billing.md`
  and the relevant tests before changing them.
