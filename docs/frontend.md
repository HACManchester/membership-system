# Frontend: Current State and Migration Plan

## Two stacks, one app

| | Legacy stack | Modern stack |
| --- | --- | --- |
| Rendering | ~94 Blade templates (`resources/views/`) | Inertia.js v3 + React (`resources/js/`) |
| Language | PHP templates + jQuery | TypeScript (6.x) + TSX |
| Styling | LESS (`resources/assets/less/application.less`), Bootstrap **3.3.4** | Material-UI (MUI) 9 |
| Entry | per-page Blade layouts | `resources/views/app.blade.php` → `react-app.tsx` → `@inertia` |
| Linting/tests | none | ESLint (flat config, `eslint.config.mts`), Prettier, `tsc` type check, Jest (`.tsx` component tests, plus two legacy `.jsx` suites); all run in CI |

Pages migrated to Inertia/React so far: the home page, courses, course training, equipment,
equipment areas, rooms, maintainer groups, the members directory, roles, newsletter, the admin
index, access lockdown and settings pages, the gift code page, the email preview index, both
notification-email forms and the member balance page. The rest (18 pages: account and profile, key
fobs and the general induction, payments/finance screens, member admin, registration and the
login/password/Discourse SSO pages) is still Blade + jQuery + Bootstrap 3.
They all use `layouts/main.blade.php`, the only live Blade layout, which loads the whole legacy
bundle.

## Build tooling

- **Vite** (`vite.config.mjs`, with `laravel-vite-plugin` and `@vitejs/plugin-react`) builds *both*
  stacks from three inputs: the legacy `resources/assets/js/app.js` and
  `resources/assets/less/application.less`, and the modern `resources/js/react-app.tsx`. Output goes
  to `public/build/` (gitignored) with a `manifest.json` that Blade's `@vite` directive reads.
- Scripts: `yarn build` (or `yarn production`) for a production build; `yarn dev` / `yarn watch`
  for an unminified build with source maps (once / on every change); `yarn hot` runs the Vite dev
  server with hot reloading, and writes `public/hot` so `@vite` serves from it while it runs.
- Local development uses **Node 22** (`.mise.toml`), as production's build does; CI uses Node 24.
  `engines` in `package.json` gives the minimum. Yarn 4 comes from Corepack (`corepack enable`),
  pinned by `packageManager` in `package.json`. Dependency install scripts are off (Yarn 4's
  default); allow one with `dependenciesMeta.<name>.built: true` only if a package truly needs it.
- Legacy-bundle quirks: Bootstrap 3, select2 3 and inline Blade scripts expect a global jQuery, so
  `app.js` imports `jquery-global.js` before them. The Bootstrap/Material icon fonts and the CSS
  images are referenced relative to the Less, so Vite bundles them into `public/build/assets/`;
  don't use root-absolute `url(/...)` in the Less, which breaks under the dev server.
- Jest compiles with Babel (`.babelrc`: preset-env, preset-react and preset-typescript, all Babel
  8); Vite doesn't use Babel. `jest.config.js` names the project's own `babel-jest`, because by
  name Jest picks the copy inside `jest-config`, which runs Babel 7 and silently skips the
  TypeScript preset.
- PHP tests don't need a frontend build: `Tests\TestCase` calls `withoutVite()`.

Note: yarn/node commands run directly on the host; PHP commands run inside the Docker container
(see CLAUDE.md / the README).

## How the modern stack is wired

`resources/views/app.blade.php` loads `react-app.tsx` through `@vite`, sets up browser-side Sentry,
and renders the `@inertia` root. Controllers return `Inertia::render('Page', [...props])`; props
are serialized via `app/Http/Resources/*` (e.g. `TrainingRecordResource`, `CourseResource`).
`config/inertia.php` tells inertia-laravel the pages are in `resources/js/Pages` (its default is
`js/pages`), which `assertInertia()` relies on to check that a page exists.

**Important convention:** Inertia sends *all* props to the browser as JSON whether or not the
component renders them — so over-fetching in a resource ships data to the client unnecessarily.
Shape props with dedicated Resources and include only the fields the page actually renders; for
anything involving member data, send the minimum (e.g. id + display name for pickers).

### What a migrated page gets from the server

- **Flash messages.** `HandleInertiaRequests` puts the message flashed with
  `->with('success'|'error', ...)` or the legacy `FlashNotification` facade into Inertia's own
  flash data as `notification: {message, severity}` (severity `success`, `info`, `warning` or
  `error`; FlashNotification's `danger` becomes `error`), and `FlashSnackbar` (in `MainLayout`)
  shows it from `usePage().flash`. Unlike a prop, Inertia's flash data isn't kept in the
  browser's history or sent with partial reloads, so a dismissed message doesn't come back on
  Back or `router.reload({ only })`. It's set for the current request only, so a Blade page
  (which shows FlashNotification itself) doesn't leave it for the next Inertia page. A
  controller that still calls `FlashNotification::success()` works for both stacks; new code
  uses `->with()`. `types/inertia.d.ts` types the flash data.
- **Validation errors.** FormRequest and `$request->validate()` failures, and the legacy
  validators' `FormValidationException` (`UserValidator`, `ProfileValidator`), reach Inertia
  forms in the session's errors bag (`errors` prop / `useForm().errors`). Blade forms still get
  them as `FlashNotification` details. Inertia validation failures flash no summary message, so
  the snackbar doesn't repeat the field errors.
- **Redirects to other sites.** An Inertia visit is an XHR and can't follow a redirect to another
  site, so `HandleInertiaRequests` turns a redirect to another host, or the same host on
  another explicit port, into `Inertia::location()` (a 409 with `X-Inertia-Location`; the
  browser then goes there itself). Prefetches keep the plain redirect, as Inertia does for
  redirects with a fragment, and the scheme isn't compared, since the proxy terminates TLS. Controllers keep using
  `redirect()->away()`; GoCardless's Direct Debit setup (`account.subscription.create`,
  `migrate-direct-debit`) needs nothing more. The Discourse SSO confirmation submits a plain
  GET form to Discourse; keep it a plain `<form>` (not an Inertia form) when it moves.
- **Paginated lists.** Send a Laravel paginator as is, with
  `->through(fn ($item) => (new SomeResource($item))->resolve())` so `data` holds only what the
  page shows; `types/pagination.ts` types it.

### Shared components

In `resources/js/Components/`, each shown on `/component-zoo`:

| Component | Replaces | Notes |
| --- | --- | --- |
| `StatusChip`, `SpaceAccessChip` | `status-label`, `space-access-label` partials | Same labels, colours and tooltips |
| `Paginator` | `paginator-links`, `user-paginator-links` partials | Every page for `paginate()`, previous/next for `simplePaginate()`; keeps the other query parameters; hidden for one page |
| `SortableHeader`, `SortLink` | `sort-by`, `sort-users-by` partials | `?sortBy=&direction=`; clicking the sorted column reverses it; keeps filters and goes back to page 1 (drops `?page=`, or the paginator's `pageParam`). `SortLink` is for a cell with two sort links |
| `TabBar` | `#mainTabBar` (e.g. `payments/partials/tabs`) | Plain links styled as tabs in a `nav`, the current one marked with `aria-current` (the controller says which); not ARIA tabs, since each is another page. Goes in `PageTitle`'s children |
| `RouterLink` | — | Inertia's `Link` for MUI's `component` prop, so MUI buttons, tabs and links navigate without a full page load |
| `FlashSnackbar` | `partials/flash-message` + `Snackbar.js` | Already in `MainLayout`; shows Inertia's flash `notification` with its severity |

### Frontend tests

Component tests live next to the components in `__tests__/*.test.tsx` and run with `yarn test`
(Jest, jsdom, React Testing Library and jest-dom). Import `describe`/`it`/`expect`/`jest` from
`@jest/globals`: there's no `@types/jest`, and jest-dom's matchers are typed for those imports.
`@inertiajs/react` ships only as an ES module, which Jest can't load, so `jest.config.js` maps it
to a stand-in (`tests/js/inertia-react.jsx`): set the page with
`jest.mocked(usePage).mockReturnValue(...)`, and `Link` renders a plain `<a>`.

## Migration plan

### Principles

1. **Page-by-page, not big-bang** — the existing pattern (a new feature lands as Inertia, its old
   Blade page dies) is working; continue it.
2. **Convert when touched** — any Blade page needing non-trivial changes gets converted instead of
   patched.
3. **No new jQuery** — anything interactive added to a Blade page during transition should be a
   small mounted React island or wait for conversion.

### Phases

Each phase is a set of independently shippable page migrations, ordered so the legacy
dependencies go one at a time.

| Phase | Pages | Removes |
| --- | --- | --- |
| P0 groundwork ✅ | none: flash messages and legacy validation errors reach Inertia pages, external redirects, the shared components above, Jest on TSX | — |
| P1 quick wins ✅ | gift, email preview, the two notification-email forms, the member balance page | the second jQuery the balance page loaded from a CDN, for a confetti effect after a cash top up; the confetti was dropped rather than ported |
| P2 finance + admin tables | the five payments screens and their tab bar, the member admin list (`account/index`) | select2 in payments; Bootstrap dropdown and tabs |
| P3 account + profile | account edit, profile edit | select2 entirely |
| P4 access | key fobs, general induction | — (door access: test carefully) |
| P5 Your Membership | `account/show` and its partials, including the admin action bar; `PaymentModule` to TSX; `AdminForms` quick updates to Inertia | Bootstrap modal and collapse, `PaymentModule`, `AdminForms`, the legacy Jest suites |
| P6 auth + registration | login, Discourse SSO, password reset ×2, registration ×2 | the last `layouts/main` users, and with them the legacy bundle |

After P5, `app.js` can shrink to the snackbar and the menu, and jQuery and Bootstrap's JS can go
before P6; Bootstrap's CSS stays until P6. Emails stay Blade (that's normal), as do the error
pages and the Inertia root.

### Tooling milestones along the way

- **Done**: Mix → Vite, Yarn 3 → 4. Jest runs in CI, on `resources/js`'s `.tsx` tests as well as
  the two legacy `.jsx` suites.
- **Done**: type-checking with `tsc` (`yarn typecheck`), which CI runs; Inertia 3, upgrading
  `@inertiajs/react` and `inertiajs/inertia-laravel` together; MUI 9.
- **When the last Blade page that uses them dies**: delete `resources/assets/` (LESS, jQuery,
  Bootstrap 3, select2) and the legacy Jest suites, removing several years of frontend
  dependencies in one commit. `FlashNotification` can then be retired, or kept only as the
  bridge above.

### Definition of done

The frontend migration is finished when `resources/assets/` is deleted (and with it the legacy
inputs in `vite.config.mjs`), Bootstrap 3 / jQuery / select2 leave `package.json`, and the only
Blade templates remaining are emails, the Inertia root, and (optionally) auth pages.
