<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('admin:create {--name=Admin : Display name} {--email= : Email address; generated when omitted} {--password= : Password; generated when omitted}')]
#[Description('Create a fresh administrator and print email:password')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: 'Admin'));
        $email = trim((string) ($this->option('email') ?: ''));
        $password = (string) ($this->option('password') ?: '');

        if ($email === '') {
            do {
                $email = 'admin-'.Str::lower(Str::random(8)).'@example.com';
            } while (User::query()->where('email', $email)->exists());
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error("A user with the email {$email} already exists. Pick another address with --email=.");

            return self::FAILURE;
        }

        $password = $password !== '' ? $password : Str::random(16);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill([
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info('Administrator created. Sign in with this email and password:');
        $this->line("{$user->email}:{$password}");

        return self::SUCCESS;
    }
}
