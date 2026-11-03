# Find A Cat: API

Laravel JSON API behind findacat.eu. It serves everything under `/api` and uses Sanctum cookie sessions for the Astro site in `../web`. There are no Blade pages.

## Requirements

PHP 8.3+ (or Docker), plus SQLite for local development and tests; production uses MySQL/MariaDB. No Node.js is needed for the API itself; the root scripts use it for the Astro site.

## Local setup

From the repository root, `npm run setup` prepares the API and `npm run dev` starts the API and the Astro site together (see `../scripts/dev`). Both use SQLite by default, so no database server is needed:

```bash
npm run setup
npm run dev      # API on :8000, Astro on :4321
```

To run the API on its own, or set it up manually:

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve        # http://localhost:8000/api/health
php artisan test
```

Without a local PHP, run the same commands through the `composer:2` Docker image (the root script does this automatically).

## Deployment

Releases for FTP/cPanel hosting are built with `../scripts/build-release` (static site, `vendor/` without dev packages, rewrite rules). Upload, fill in `.env` and run `/api/install?token=…`. The full walkthrough is in the docs: *Installing on shared hosting*.

On Vercel, `index.php` in this folder is the function entry (see `../vercel.json`); `bootstrap/vercel.php` sets read-only-filesystem defaults before booting Laravel, and the build runs `php artisan vercel:build` (migrations and generated secrets). Walkthrough: *Deploying on Vercel*.

`composer.json` pins `config.platform.php` to 8.3 so the lock file also works on PHP 8.3 hosts. Keep it that way when running `composer update`.
