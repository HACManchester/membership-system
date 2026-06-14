# Frontend: Current State and Migration Plan

## Two stacks, one app

| | Legacy stack | Modern stack |
| --- | --- | --- |
| Rendering | ~121 Blade templates (`resources/views/`) | Inertia.js v2 + React (`resources/js/`) |
| Language | PHP templates + jQuery | TypeScript (5.x) + TSX |
| Styling | LESS (`resources/assets/less/application.less`), Bootstrap **3.3.4** | Material-UI (MUI) 6 |
| Entry | per-page Blade layouts | `resources/views/app.blade.php` → `react-app.tsx` → `@inertia` |
| Linting/tests | none | ESLint (flat config, `eslint.config.mts`), Prettier, Jest (configured, no tests yet) |

Pages migrated to Inertia/React so far: courses, course training, equipment-adjacent pages,
newsletter. Everything else (account, profile, members directory, payments/admin screens, storage
boxes, stats) is still Blade + jQuery + Bootstrap 3.

## Build tooling

- **Laravel Mix 6** (`webpack.mix.js`) compiles *both* stacks: the legacy LESS + `resources/assets/js`
  bundle and the modern `resources/js/react-app.tsx` bundle.
- `elixir.json` is a leftover from the pre-Mix build — dead, delete it.
- Node 16 in the Dockerfile (EOL) — bump to current LTS.
- No Vite. Mix works, but Vite is the Laravel default since 9.x and brings HMR + much faster
  builds; the swap is mechanical for an Inertia app (`vite.config.ts`, `@vite` directive replaces
  `mix()` helpers).

Note: yarn/node commands run directly on the host; PHP commands run inside the Docker container
(see CLAUDE.md / the README).

## How the modern stack is wired

`resources/views/app.blade.php` loads the Mix-built `react-app.js`, sets up browser-side Sentry,
and renders the `@inertia` root. Controllers return `Inertia::render('Page', [...props])`; props
are serialized via `app/Http/Resources/*` (e.g. `InductionResource`, `CourseResource`).

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

- **Now**: delete `elixir.json`; bump Node. (The no-op Jest CI step has been removed; reinstate a
  JS test step once `resources/js` actually has tests.)
- **With the Laravel 9 hop**: switch Mix → Vite (the natural moment, since Laravel 9 ships Vite).
- **When the last Blade page that uses them dies**: delete `resources/assets/` (LESS, jQuery,
  Bootstrap 3, select2), removing several years of frontend dependencies in one commit.

### Definition of done

The frontend migration is finished when `resources/assets/` is deleted, `webpack.mix.js` is
replaced by `vite.config.ts`, Bootstrap 3 / jQuery / select2 leave `package.json`, and the only
Blade templates remaining are emails, the Inertia root, and (optionally) auth pages.
