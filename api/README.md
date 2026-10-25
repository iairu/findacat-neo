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

Releases for FTP/cPanel hosting are built with `../scripts/build-release` (static site, `vendor/` without dev packages, rewrite rules). Upload, fill in `.env` and run `/api/install?token=…`. The full walkthrough is in the docs: *Installing on shared hosting*.

`composer.json` pins `config.platform.php` to 8.3 so the lock file also works on PHP 8.3 hosts. Keep it that way when running `composer update`.
