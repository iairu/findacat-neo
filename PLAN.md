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
3. **Inbreeding:** PHP Wright COI service ported from `inbreeding.js`; tests compare against old outputs on the demo data.
4. **API:** accounts (sign up, verification, password reset), cats, parents, search, pedigree, test mating, birthdays, PawPeds.
5. **Portal UI:** search, cat profile, pedigree tree and chart, test mating with COI, "My cats", cat registration.
6. **Admin:** moderation queue, backups, CSV import/export, breed/EMS management.
7. **Docs and release:** docs content, release script, remove `old_project`.

## Open checks

- PawPeds: confirm whether the old integration scrapes pages (fragile) before porting it.
- License: old code derives from Silsilah (MIT); keep the notice wherever its logic is carried over.
