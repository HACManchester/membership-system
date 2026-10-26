# Frontend: Current State and Migration Plan

## Two stacks, one app

| | Legacy stack | Modern stack |
| --- | --- | --- |
| Rendering | ~121 Blade templates (`resources/views/`) | Inertia.js v2 + React (`resources/js/`) |
| Language | PHP templates + jQuery | TypeScript (5.x) + TSX |
| Styling | LESS (`resources/assets/less/application.less`), Bootstrap **3.3.4** | Material-UI (MUI) 6 |
| Entry | per-page Blade layouts | `resources/views/app.blade.php` → `react-app.tsx` → `@inertia` |
| Linting/tests | none | ESLint (flat config, `eslint.config.mts`), Prettier, Jest (two legacy `.jsx` component suites; runs in CI) |

Pages migrated to Inertia/React so far: courses, course training, equipment-adjacent pages,
newsletter. Everything else (account, profile, members directory, payments/admin screens, storage
boxes, stats) is still Blade + jQuery + Bootstrap 3.

## Build tooling

- **Vite** (`vite.config.mjs`, with `laravel-vite-plugin` and `@vitejs/plugin-react`) builds *both*
  stacks from three inputs: the legacy `resources/assets/js/app.js` and
  `resources/assets/less/application.less`, and the modern `resources/js/react-app.tsx`. Output goes
  to `public/build/` (gitignored) with a `manifest.json` that Blade's `@vite` directive reads.
- Scripts: `yarn build` (or `yarn production`) for a production build; `yarn dev` / `yarn watch`
  for an unminified build with source maps (once / on every change); `yarn hot` runs the Vite dev
  server with hot reloading, and writes `public/hot` so `@vite` serves from it while it runs.
- Vite 8 needs **Node 20.19+** (CI uses Node 24). Yarn 4 comes from Corepack (`corepack enable`),
  pinned by `packageManager` in `package.json`. Dependency install scripts are off (Yarn 4's
  default); allow one with `dependenciesMeta.<name>.built: true` only if a package truly needs it.
- Legacy-bundle quirks: Bootstrap 3, select2 3 and inline Blade scripts expect a global jQuery, so
  `app.js` imports `jquery-global.js` before them. The Bootstrap/Material icon fonts and the CSS
  images are referenced relative to the Less, so Vite bundles them into `public/build/assets/`;
  don't use root-absolute `url(/...)` in the Less, which breaks under the dev server.
- Jest still compiles with Babel (`.babelrc`, `babel-jest`); Vite doesn't use Babel.
- PHP tests don't need a frontend build: `Tests\TestCase` calls `withoutVite()`.

Note: yarn/node commands run directly on the host; PHP commands run inside the Docker container
(see CLAUDE.md / the README).

## How the modern stack is wired

`resources/views/app.blade.php` loads `react-app.tsx` through `@vite`, sets up browser-side Sentry,
and renders the `@inertia` root. Controllers return `Inertia::render('Page', [...props])`; props
are serialized via `app/Http/Resources/*` (e.g. `TrainingRecordResource`, `CourseResource`).

**Important convention:** Inertia sends *all* props to the browser as JSON whether or not the
component renders them — so over-fetching in a resource ships data to the client unnecessarily.
Shape props with dedicated Resources and include only the fields the page actually renders; for
anything involving member data, send the minimum (e.g. id + display name for pickers).

## Migration plan

### Principles

1. **Page-by-page, not big-bang** — the existing pattern (a new feature lands as Inertia, its old
   Blade page dies) is working; continue it.
2. **Convert when touched** — any Blade page needing non-trivial changes gets converted instead of
   patched.
3. **No new jQuery** — anything interactive added to a Blade page during transition should be a
   small mounted React island or wait for conversion.

### Suggested order (value ÷ effort)

1. **Member-facing, high-traffic, simple**: members directory, leaderboard — small read-mostly
   pages, good warm-ups.
2. **Account/profile cluster**: registration, profile edit, account settings. Biggest UX win;
   pairs with splitting `AccountController` (architecture.md). Includes the photo-upload/approval
   flow.
3. **Admin screens**: member admin list + detail (the 389-line
   `member-admin-action-bar.blade.php` is the largest legacy template in the repo), payments/
   finance screens, stats.
4. **Long tail**: emails stay Blade (that's normal); auth pages (login/reset) can stay Blade
   indefinitely or convert last.

### Tooling milestones along the way

- **Done**: Mix → Vite, Yarn 3 → 4. (Jest runs in CI again, covering the two legacy `.jsx`
  suites; `resources/js` has no tests yet.)
- **When the last Blade page that uses them dies**: delete `resources/assets/` (LESS, jQuery,
  Bootstrap 3, select2), removing several years of frontend dependencies in one commit.

### Definition of done

The frontend migration is finished when `resources/assets/` is deleted (and with it the legacy
inputs in `vite.config.mjs`), Bootstrap 3 / jQuery / select2 leave `package.json`, and the only
Blade templates remaining are emails, the Inertia root, and (optionally) auth pages.
