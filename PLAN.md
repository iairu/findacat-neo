# Find A Cat Neo: rewrite plan

Rewrite of `old_project/` (Laravel 8, Silsilah fork) into a static Astro site plus a Laravel JSON API.

## Decisions

- **Backend:** current Laravel, JSON API only, Sanctum SPA cookie auth on the same domain under `/api`.
- **Frontend:** static Astro build (`web/`, renamed from `docs-astro/`): homepage, docs and portal. Portal pages are interactive islands calling the API.
- **Hosting:** FTP/cPanel only. No SSH, Composer or Node on the server. Releases ship with `vendor/` included; migrations run through a token-protected installer route.
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
├─ scripts/      release builder for FTP deploys
└─ old_project/  reference only; removed once features are matched
```

## Deployment (FTP/cPanel)

`scripts/build-release` produces a zip to upload as-is:

- The Astro build sits at the root of `public_html`; `/api/*` routes to Laravel's `index.php`.
- Laravel core and `vendor/` go outside `public_html` where the host allows it, otherwise in a folder locked by a deny-all `.htaccess`.
- First-run migrations and the first admin are created by a one-time, token-protected `/api/install` page. Scheduled work uses cPanel cron; mail uses SMTP.
- Pages keyed by ID (`/cats/?id=...`) are one static page that loads its data from the API.

## Phases

1. **Base setup:** rename the Astro app and strip PressHaven content; amber/cream theme (light + dark); EN/SK routing; bare Laravel API with Sanctum.
2. **Data:** migrations keeping old UUIDs and fields, plus `owner_id` and moderation fields; importer for the old findacat.eu database; breeds/EMS.
3. **Inbreeding:** PHP Wright COI service written from the published method (not translated from `inbreeding.js`, which is GPL-3.0); tests compare against old outputs on the demo data.
4. **API:** accounts (sign up, verification, password reset), cats, parents, search, pedigree, test mating, birthdays, PawPeds.
5. **Portal UI:** search, cat profile, pedigree tree and chart, test mating with COI, "My cats", cat registration.
6. **Admin:** moderation queue, backups, CSV import/export, breed/EMS management.
7. **Docs and release:** docs content, release script, remove `old_project`.

## Open checks

- PawPeds: confirm whether the old integration scrapes pages (fragile) before porting it.
- License: old code derives from Silsilah (MIT); keep the notice wherever its logic is carried over.
- License (GPL): `old_project/src/public/js/inbreeding.js` is Sam Kauffman's Inbreeding Calculator, GPL-3.0. Translating it to PHP would make the port GPL. Write the COI service from Wright's formula and use the old script only as a black-box oracle for test expectations, or decide deliberately to license the project GPL.
- Hosting: Laravel 13 needs PHP 8.3+. Confirm the target host offers it before phase 4.

## Status

- Phase 1 done: `web/` (Astro, amber theme, EN/SK, docs with search) and `api/` (Laravel 13, Sanctum cookie auth, `/api/health`).
- Phase 2 done: breeds, EMS colours and cats tables (legacy ids kept, plus `owner_id` and `moderation_status`), and `php artisan legacy:import --breeds= --ems= --cats=` for the old CSV exports. It drops the self-parented placeholder cat, nulls unknown dates and parents, and breaks ancestry cycles, reporting each as a warning. Not imported: old `cat_metadata`, `couples` and users (they re-register).
- Phase 3 done: `App\Services\Inbreeding` (clean-room Wright path method via kinship recursion, tested against textbook values: full sibs 0.25, half sibs 0.125, cousins 0.0625, inbred ancestor 0.15625). Comparison against the old inbreeding.js as a black-box oracle is still open.
- Local PHP is not installed; run Composer and Artisan through the `composer:2` Docker image (see `api/README.md`).
