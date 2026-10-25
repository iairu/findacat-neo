<?php

namespace App\Console\Commands;

use App\Models\User;
use Closure;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/**
 * Runs during the Vercel build (see the root composer.json): migrates the database and writes
 * the generated APP_KEY and setup token into the bundle, keeping them in the database so every
 * deployment gets the same values. Variables set in the Vercel project take precedence.
 */
#[Signature('vercel:build {--output= : where to write the secrets file (default bootstrap/cache/vercel-secrets.php)}')]
#[Description('Migrate the database and prepare generated secrets for a Vercel deployment')]
class VercelBuild extends Command
{
    public function handle(): int
    {
        $output = $this->option('output') ?: base_path('bootstrap/cache/vercel-secrets.php');

        if (filled(config('app.key')) && ! $this->isValidKey((string) config('app.key'))) {
            $this->error('APP_KEY is set in the project but is not a valid '.config('app.cipher').' key, so every request would fail.');
            $this->error('Delete it under Settings → Environment Variables to have one generated, or set "base64:" followed by the output of `openssl rand -base64 32`, without quotes.');

            return self::FAILURE;
        }

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            File::put($output, "<?php\n\nreturn [];\n");
            $this->warn('No database reachable, so migrations and secrets were skipped: '.$e->getMessage());
            $this->warn('Connect a database (Storage → Neon) and redeploy.');

            return self::SUCCESS;
        }

        if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $secrets = [];
        if (blank(config('app.key'))) {
            $secrets['APP_KEY'] = $this->storedSecret('app_key', fn (): string => 'base64:'.base64_encode(Encrypter::generateKey(config('app.cipher'))));
        }

        $needsAdmin = ! User::query()->where('is_admin', true)->exists();
        if ($needsAdmin && blank(config('app.install_token'))) {
            $secrets['INSTALL_TOKEN'] = $this->storedSecret('install_token', fn (): string => Str::random(40));
            $secrets['INSTALL_FIRST_RUN_ONLY'] = 'true';
        }

        File::put($output, "<?php\n\nreturn ".var_export($secrets, true).";\n");

        if ($needsAdmin) {
            $token = $secrets['INSTALL_TOKEN'] ?? '<your INSTALL_TOKEN>';
            $this->info('Create the first administrator at '.rtrim((string) config('app.url'), '/')."/api/install?token={$token}");
        }

        return self::SUCCESS;
    }

    private function isValidKey(string $key): bool
    {
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7), true);
        }

        return $key !== false && Encrypter::supported($key, config('app.cipher'));
    }

    /**
     * The stored secret, created on first use. Parallel builds race on the insert; both read back the winner.
     *
     * @param  Closure(): string  $generate
     */
    private function storedSecret(string $name, Closure $generate): string
    {
        DB::table('app_secrets')->insertOrIgnore(['name' => $name, 'value' => $generate()]);

        return DB::table('app_secrets')->where('name', $name)->value('value');
    }
}
