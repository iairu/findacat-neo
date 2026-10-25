<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Requests from the Astro origin get the session, as in production.
        $this->withHeaders(['Origin' => 'http://localhost', 'Referer' => 'http://localhost/']);
    }

    private array $new = ['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'correct-horse-9', 'password_confirmation' => 'correct-horse-9'];

    public function test_register_logs_in_and_sends_a_verification_email(): void
    {
        Notification::fake();

        $this->postJson('/api/register', $this->new)->assertCreated()
            ->assertJsonPath('data.verified', false)->assertJsonPath('data.is_admin', false);

        $user = User::firstWhere('email', 'ann@example.com');
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->getJson('/api/user')->assertOk()->assertJsonPath('data.email', 'ann@example.com');
    }

    public function test_register_rejects_weak_passwords_and_duplicates(): void
    {
        $this->postJson('/api/register', ['password' => 'short', 'password_confirmation' => 'short'] + $this->new)
            ->assertStatus(422)->assertJsonValidationErrors('password');
        User::factory()->create(['email' => 'ann@example.com']);
        $this->postJson('/api/register', $this->new)->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_and_logout(): void
    {
        User::factory()->create(['email' => 'bob@example.com', 'password' => 'correct-horse-9']);

        $this->postJson('/api/login', ['email' => 'bob@example.com', 'password' => 'wrong'])->assertStatus(422);
        $this->postJson('/api/login', ['email' => 'bob@example.com', 'password' => 'correct-horse-9'])->assertOk();
        $this->getJson('/api/user')->assertOk();

        $this->postJson('/api/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_signed_link_verifies_the_email(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->get($url)->assertRedirect(config('app.frontend_url').'/account?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_tampered_verification_links_are_refused(): void
    {
        $user = User::factory()->unverified()->create();
        $this->get("/api/email/verify/{$user->id}/".sha1($user->email))->assertForbidden();

        $other = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => 'bad']);
        $this->get($other)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_password_reset_flow_does_not_reveal_accounts(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com'])->assertStatus(202);
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(202);

        Notification::assertSentTo($user, ResetPassword::class, function ($n) use ($user) {
            $this->postJson('/api/reset-password', [
                'token' => $n->token, 'email' => $user->email,
                'password' => 'brand-new-pass-1', 'password_confirmation' => 'brand-new-pass-1',
            ])->assertOk();

            return str_starts_with($n->toMail($user)->actionUrl, config('app.frontend_url').'/reset-password?token=');
        });
        $this->assertTrue(\Hash::check('brand-new-pass-1', $user->fresh()->password));
    }

    public function test_profile_update_needs_the_current_password_to_change_it(): void
    {
        $user = User::factory()->create(['password' => 'correct-horse-9']);
        $this->actingAs($user, 'web');

        $this->patchJson('/api/user', ['locale' => 'sk'])->assertOk()->assertJsonPath('data.locale', 'sk');
        $this->patchJson('/api/user', ['password' => 'another-long-one-1', 'password_confirmation' => 'another-long-one-1'])
            ->assertStatus(422);
        $this->patchJson('/api/user', ['password' => 'another-long-one-1', 'password_confirmation' => 'another-long-one-1', 'current_password' => 'correct-horse-9'])
            ->assertOk();
    }
}
