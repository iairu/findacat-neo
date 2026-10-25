<?php

/*
 * Front controller for Vercel (see vercel.json at the repository root).
 *
 * Vercel runs this file as a serverless function for every /api request. The defaults for
 * settings the project does not set live in bootstrap/vercel.php.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/bootstrap/vercel.php';

// The PHP built-in server reports the router as the script; without this Symfony strips "/api" from every path.
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
