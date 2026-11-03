<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_admin_and_prints_credentials(): void
    {
        $this->assertSame(0, Artisan::call('admin:create'));
        $output = trim(Artisan::output());

        $this->assertMatchesRegularExpression('/^([^\s:]+@[^\s:]+):(\S+)$/m', $output);
        preg_match('/^([^\s:]+@[^\s:]+):(\S+)$/m', $output, $match);

        $user = User::firstWhere('email', $match[1]);
        $this->assertNotNull($user);
        $this->assertSame('Admin', $user->name);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check($match[2], $user->password));
    }

    public function test_it_accepts_explicit_credentials_and_refuses_duplicate_emails(): void
    {
        $this->assertSame(0, Artisan::call('admin:create', [
            '--name' => 'Root',
            '--email' => 'root@example.com',
            '--password' => 'a-long-password',
        ]));

        $user = User::firstWhere('email', 'root@example.com');
        $this->assertSame('Root', $user->name);
        $this->assertTrue(Hash::check('a-long-password', $user->password));

        $this->assertSame(1, Artisan::call('admin:create', ['--email' => 'root@example.com']));
        $this->assertStringContainsString('already exists', Artisan::output());
        $this->assertSame(1, User::count());
    }
}
