<?php

/*
 * Defaults for Vercel, loaded by index.php at run time and by bootstrap/app.php during the build.
 *
 * The function's filesystem is read-only apart from /tmp, and settings come from the project's
 * environment variables, so anything not set there falls back to the values below. APP_KEY and
 * the setup token come from bootstrap/cache/vercel-secrets.php, written by `artisan vercel:build`.
 */

(static function (): void {
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

    $secretsFile = __DIR__.'/cache/vercel-secrets.php';
    // Only at run time: the build regenerates the file and must see what the project itself sets.
    $secrets = PHP_SAPI !== 'cli' && is_file($secretsFile) ? require $secretsFile : [];

    foreach ([
        ...$secrets,
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
})();
