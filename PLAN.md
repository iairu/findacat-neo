# Find A Cat Neo: rewrite plan

Rewrite of the old Laravel 8 app (a Silsilah fork, removed from the tree in phase 7; see git history before commit `Phase 7`) into a static Astro site plus a Laravel JSON API.

## Decisions

- **Backend:** current Laravel, JSON API only, Sanctum SPA cookie auth on the same domain under `/api`.
- **Frontend:** static Astro build (`web/`, renamed from `docs-astro/`): homepage, docs and portal. Portal pages are interactive islands calling the API.
- **Hosting:** FTP/cPanel only. No SSH, Composer or Node on the server. Releases ship with `vendor/` included; migrations run through a token-protected installer route. Vercel (Git import) is supported as a second target.
- **Registration:** public accounts with email verification. Users register and edit their own cats. Admins moderate and manage breeds, EMS and backups.
- **Palette:** warm amber/cream (cat-coat tones) with light and dark modes, replacing neon green on black.
- **Carry over:** import of the findacat.eu database (keep UUIDs), EN + SK localization, PawPeds integration, backups and CSV import/export, inbreeding (COI) for test matings and pedigrees.
- **COI:** PHP service in the API, regression-tested against outputs of the old `public/js/inbreeding.js`.
- **Docs:** user guide, breeding/genetics background (from the thesis), admin and self-hosting, API reference.

## Layout

```
findacat-neo/
├─ api/          Laravel, JSON API only
├─ web/          Astro: homepage, docs, portal
└─ scripts/      release builder for FTP deploys
vercel.json, composer.json   Vercel build: static site + api/index.php as a PHP function
```

## Deployment (FTP/cPanel)

`scripts/build-release` produces a zip to upload as-is:

- The Astro build sits at the root of `public_html`; `/api/*` routes to Laravel's `index.php`.
- Laravel core and `vendor/` go outside `public_html` where the host allows it, otherwise in a folder locked by a deny-all `.htaccess`.
- First-run migrations and the first admin are created by a one-time, token-protected `/api/install` page. Scheduled work uses cPanel cron; mail uses SMTP.
- Pages keyed by ID (`/cats/?id=...`) are one static page that loads its data from the API.

## Deployment (Vercel)

The Deploy button in `README.md` clones the repository, prefills `APP_NAME`, `DISABLE_SIGNUPS` and `MAIL_MAILER`, and preselects Neon Postgres; the root `package.json` lists `astro` so the import flow detects the project at the root; a plain Git import works too, with Neon added under Storage. No dashboard settings or variables are required:

- `vercel.json` builds `web/` to `web/dist` and runs `api/index.php` with the `vercel-php` runtime; `/api` and `/api/*` rewrite to it.
- The runtime only looks for `composer.json` at the root, so the root `composer.json` has a `vercel` script that installs `api/` without dev packages.
- `api/bootstrap/vercel.php` (loaded by `api/index.php`, and by `bootstrap/app.php` when `VERCEL` is set) fills in Vercel defaults when a variable is unset: storage and bootstrap caches in `/tmp`, logs to stderr, database sessions and cache, trusted proxies, `APP_URL` from `VERCEL_*`, and `DB_URL` from a Marketplace `DATABASE_URL`/`POSTGRES_URL`.
- The root `composer.json` build hook then runs `artisan vercel:build`: migrations, plus `APP_KEY` and a setup token generated once into the `app_secrets` table and written to `bootstrap/cache/vercel-secrets.php` in the bundle. While no admin exists the build log prints the setup link; the generated token only works until an admin exists (`INSTALL_FIRST_RUN_ONLY`).
- The database is external (Neon Postgres or MySQL). Only mail settings need to be set by hand; `.env.example` at the root lists the variables.

## Phases

1. **Base setup:** rename the Astro app and strip PressHaven content; amber/cream theme (light + dark); EN/SK routing; bare Laravel API with Sanctum.
2. **Data:** migrations keeping old UUIDs and fields, plus `owner_id` and moderation fields; importer for the old findacat.eu database; breeds/EMS.
3. **Inbreeding:** PHP Wright COI service written from the published method (not translated from `inbreeding.js`, which is GPL-3.0); tests compare against old outputs on the demo data.
4. **API:** accounts (sign up, verification, password reset), cats, parents, search, pedigree, test mating, birthdays, PawPeds.
5. **Portal UI:** search, cat profile, pedigree tree and chart, test mating with COI, "My cats", cat registration.
6. **Admin:** moderation queue, backups, CSV import/export, breed/EMS management.
7. **Docs and release:** docs content, release script, remove `old_project`.

## Open checks

- Phase 5 done: portal pages in `web/` (EN and SK): cat search, cat page with inbreeding and pedigree tree, test mating, birthdays, sign in/register/reset, account with own cats, and a cat editor. They are static shells that call `/api` from the browser (`web/src/lib/portal.ts`); `astro dev` proxies `/api` to `localhost:8000`. Verified in a headless browser against a seeded API; no automated browser tests are committed yet.
- Phase 6 done: admin API (`/api/admin/breeds`, `/ems`, `/users`, `/backup/{cats|breeds|ems}` for CSV export, import and clear with `confirm=DELETE`) and an admin page in `web/` (approval queue, breeds, EMS colours, users, backup). Exports use the legacy CSV layout plus `owner_id` and `moderation_status`, which an import restores when present; 47 API tests pass. Driven end to end in a headless browser (queue, breeds, EMS, users, CSV round trip).
- PawPeds: confirm whether the old integration scrapes pages (fragile) before porting it.
- License: old code derives from Silsilah (MIT); keep the notice wherever its logic is carried over.
- License (GPL): the old `public/js/inbreeding.js` (in git history) is Sam Kauffman's Inbreeding Calculator, GPL-3.0. Translating it to PHP would make the port GPL. Write the COI service from Wright's formula and use the old script only as a black-box oracle for test expectations, or decide deliberately to license the project GPL.
- Hosting: Laravel 13 needs PHP 8.3+. Confirm the target host offers it before phase 4.

## Status

- Phase 1 done: `web/` (Astro, amber theme, EN/SK, docs with search) and `api/` (Laravel 13, Sanctum cookie auth, `/api/health`).
- Phase 2 done: breeds, EMS colours and cats tables (legacy ids kept, plus `owner_id` and `moderation_status`), and `php artisan legacy:import --breeds= --ems= --cats=` for the old CSV exports. It drops the self-parented placeholder cat, nulls unknown dates and parents, and breaks ancestry cycles, reporting each as a warning. Not imported: old `cat_metadata`, `couples` and users (they re-register).
- Phase 3 done: `App\Services\Inbreeding` (clean-room Wright path method via kinship recursion, tested against textbook values: full sibs 0.25, half sibs 0.125, cousins 0.0625, inbred ancestor 0.15625). Comparison against the old inbreeding.js as a black-box oracle is still open.
- Phase 4 done except PawPeds: auth (register, login, logout, email verification, password reset, profile), cats (search, show with COI and kittens, pedigree tree, create/update/delete by owner or admin with parent sex and cycle checks), breeds, EMS, stats, birthdays and test mating, 39 tests. New cats by regular users start as `pending` and are only visible to their owner and admins until approved; cats created by admins are approved at once. Admin endpoints: `GET /api/admin/cats?status=`, `POST /api/admin/cats/{id}/approve|reject` (the admin UI comes in phase 6). Owners see their cats at `GET /api/my/cats`.
- Phase 5 done: portal pages in `web/` (EN and SK): cat search, cat page with inbreeding and pedigree tree, test mating, birthdays, sign in/register/reset, account with own cats, and a cat editor. They are static shells that call `/api` from the browser (`web/src/lib/portal.ts`); `astro dev` proxies `/api` to `localhost:8000`. Verified in a headless browser against a seeded API; no automated browser tests are committed yet. Admin screens (moderation queue UI, backups, CSV, breed/EMS editing) are phase 6.
- PawPeds: `old_project` calls a `PawPedsService` that does not exist in the repo (only the controller and a JS button), so the old integration never worked. PawPeds' robots.txt disallows `/db/`, where the pedigree database lives, so an automated fetcher is on hold until PawPeds agrees to it.
- Local PHP is not installed; run Composer and Artisan through the `composer:2` Docker image (see `api/README.md`).
- Phase 7 done: docs (user guide, installing on shared hosting, running the site, API reference; EN and SK), `scripts/build-release` (static site + `findacat-app/` with production `vendor/`, `.htaccess` routing `/api` to Laravel, generated `APP_KEY` and `INSTALL_TOKEN`), and the token-protected `/api/install` route (migrations, first admin, re-run for upgrades). Checked end to end: the zip served by Apache on PHP 8.3 installs, signs in, adds and finds a cat. `old_project/` is removed except the demo CSVs, now in `api/database/demo/`; the thesis PDFs are only in git history. Dependencies are pinned to PHP 8.3 (`config.platform.php`) because the latest Symfony needs 8.4.
- Vercel: `vercel.json`, root `composer.json` build hook, `api/index.php` entry. Searches use case-insensitive `whereLike` and the birth filter uses date ranges, so they work on PostgreSQL; the cats table adds its self-referencing keys after creation for the same reason; the install route skips sessions and throttles through the file store so it runs before the sessions and cache tables exist; Sanctum always treats the requested host as stateful (preview URLs). Checked: the suite passes on SQLite, PostgreSQL 17 and MySQL 8.4, and `vercel build` output, run on Amazon Linux 2023 against PostgreSQL, installs, signs in, adds and finds a cat.
- Not done: PawPeds (see above), a committed browser test suite, comparing COI against the old `inbreeding.js` on real pedigrees.
