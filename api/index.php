<?php

/*
 * Front controller for Vercel (see vercel.json at the repository root).
 *
 * Vercel runs this file as a serverless function for every /api request. The function's
 * filesystem is read-only apart from /tmp, and settings come from the project's environment
 * variables, so anything not set there falls back to the defaults below.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$env = static fn (string $key): ?string => ($value = getenv($key)) === false || $value === '' ? null : $value;
$default = static function (string $key, ?string $value) use ($env): void {
    if ($value !== null && $env($key) === null) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
};

$host = $env('VERCEL_ENV') === 'production' ? $env('VERCEL_PROJECT_PRODUCTION_URL') : $env('VERCEL_URL');
$siteUrl = $host === null ? null : "https://{$host}";

// A database added through the Vercel Marketplace (Neon, Supabase, …) sets one of these.
$databaseUrl = $env('DB_URL') ?? $env('DATABASE_URL') ?? $env('POSTGRES_URL');
$driver = match (strtolower((string) parse_url((string) $databaseUrl, PHP_URL_SCHEME))) {
    'postgres', 'postgresql', 'pgsql' => 'pgsql',
    'mysql', 'mariadb' => 'mysql',
    default => null,
};

foreach ([
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => $siteUrl,
    'FRONTEND_URL' => $env('APP_URL') ?? $siteUrl,
    'DB_URL' => $databaseUrl,
    'DB_CONNECTION' => $driver,
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'database',
    'SESSION_SECURE_COOKIE' => 'true',
    'CACHE_STORE' => 'database',
    'QUEUE_CONNECTION' => 'sync',
    'TRUSTED_PROXIES' => '*',
    'LARAVEL_STORAGE_PATH' => '/tmp/storage',
    'APP_CONFIG_CACHE' => '/tmp/bootstrap/config.php',
    'APP_EVENTS_CACHE' => '/tmp/bootstrap/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/bootstrap/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/services.php',
] as $key => $value) {
    $default($key, $value);
}

foreach (['/tmp/bootstrap', '/tmp/storage/app', '/tmp/storage/framework/cache/data', '/tmp/storage/framework/sessions', '/tmp/storage/framework/views', '/tmp/storage/logs'] as $dir) {
    is_dir($dir) || mkdir($dir, 0755, true);
}

// The PHP built-in server reports the router as the script; without this Symfony strips "/api" from every path.
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
