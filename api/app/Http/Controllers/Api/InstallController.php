<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

/**
 * One-time setup for hosts without SSH: runs migrations and creates the first administrator.
 * Guarded by the INSTALL_TOKEN from `.env`; with no token configured the routes do not exist.
 * Posting only the token again later runs new migrations after an upgrade.
 */
class InstallController extends Controller
{
    public function form(Request $request): Response
    {
        $this->authorizeToken((string) $request->query('token'));
        $needsAdmin = $this->needsAdmin();
        $token = e((string) $request->query('token'));

        $fields = $needsAdmin
            ? '<label>Name <input name="name" required></label><label>Email <input name="email" type="email" required></label>'
              .'<label>Password (12+ characters) <input name="password" type="password" minlength="12" required></label>'
            : '<p>An administrator already exists. This will only run pending migrations.</p>';

        return response(<<<HTML
            <!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Find A Cat setup</title>
            <style>body{font:16px system-ui;max-width:28rem;margin:3rem auto;padding:0 1rem}label{display:block;margin:1rem 0}input{display:block;width:100%;padding:.5rem}button{padding:.6rem 1.2rem}pre{white-space:pre-wrap}</style>
            <h1>Find A Cat setup</h1>
            <form id="f">{$fields}<input type="hidden" name="token" value="{$token}"><button>Run setup</button></form>
            <pre id="out"></pre>
            <script>
            f.onsubmit = async (e) => {
              e.preventDefault();
              const r = await fetch('/api/install', {method: 'POST', headers: {'Content-Type': 'application/json', Accept: 'application/json'}, body: JSON.stringify(Object.fromEntries(new FormData(f)))});
              const d = await r.json().catch(() => ({}));
              out.textContent = r.ok ? 'Done. Remove INSTALL_TOKEN from .env now.\\n' + d.migrations : (d.message || 'Failed') + '\\n' + JSON.stringify(d.errors || {}, null, 2);
            };
            </script>
            HTML);
    }

    public function run(Request $request): JsonResponse
    {
        $this->authorizeToken((string) $request->input('token'));

        $needsAdmin = $this->needsAdmin();
        $data = $needsAdmin ? $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => ['required', Password::min(12)],
        ]) : [];

        Artisan::call('migrate', ['--force' => true]);
        $migrations = trim(Artisan::output());

        if ($needsAdmin) {
            User::query()->updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
            ])->forceFill(['is_admin' => true, 'email_verified_at' => now()])->save();
        }

        return response()->json(['migrations' => $migrations, 'admin_created' => $needsAdmin]);
    }

    /** True until an admin exists; a missing `users` table means a fresh database. */
    private function needsAdmin(): bool
    {
        return ! Schema::hasTable('users') || ! User::query()->where('is_admin', true)->exists();
    }

    private function authorizeToken(string $given): void
    {
        $token = (string) config('app.install_token');
        abort_if($token === '', 404);
        abort_unless(hash_equals($token, $given), 403, 'Invalid install token.');
    }
}
