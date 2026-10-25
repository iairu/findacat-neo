# Find A Cat: API

Laravel JSON API behind findacat.eu. It serves everything under `/api` and uses Sanctum cookie sessions for the Astro site in `../web`. There are no Blade pages.

## Requirements

PHP 8.3+, MySQL/MariaDB (SQLite is used for tests). No Node.js is needed.

## Local setup

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve        # http://localhost:8000/api/health
php artisan test
```

Without a local PHP, run the same commands through the `composer:2` Docker image.

## Deployment

The target is FTP/cPanel hosting, so releases are built from `../scripts` with `vendor/` included. See `../PLAN.md`.
