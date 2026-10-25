<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Set the application's locale based on session or other logic
        app()->setLocale(session('locale', config('app.locale')));

        return $next($request);
    }
}
