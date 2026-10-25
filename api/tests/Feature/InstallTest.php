<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    private array $admin = ['name' => 'Root', 'email' => 'root@example.com', 'password' => 'a-long-passphrase-1'];

    protected function setUp(): void
    {
        parent::setUp();
        // The installer throttles through the file store; keep attempts from leaking between runs.
        config(['cache.stores.file' => ['driver' => 'array']]);
    }

    public function test_routes_do_not_exist_without_a_token(): void
    {
        config(['app.install_token' => null]);

        $this->getJson('/api/install')->assertNotFound();
        $this->postJson('/api/install', ['token' => ''] + $this->admin)->assertNotFound();
    }

    public function test_wrong_token_is_rejected(): void
    {
        config(['app.install_token' => 'secret-token']);

        $this->postJson('/api/install', ['token' => 'nope'] + $this->admin)->assertForbidden();
        $this->assertSame(0, User::count());
    }

    public function test_creates_a_verified_admin_once(): void
    {
        config(['app.install_token' => 'secret-token']);

        $this->postJson('/api/install', ['token' => 'secret-token'] + $this->admin)
            ->assertOk()->assertJsonPath('admin_created', true);
        $admin = User::where('email', 'root@example.com')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->hasVerifiedEmail());

        $this->postJson('/api/install', ['token' => 'secret-token', 'email' => 'other@example.com'])
            ->assertOk()->assertJsonPath('admin_created', false);
        $this->assertSame(1, User::count());
    }

    public function test_weak_password_is_rejected_and_form_renders(): void
    {
        config(['app.install_token' => 'secret-token']);

        $this->postJson('/api/install', ['token' => 'secret-token', 'password' => 'short'] + $this->admin)
            ->assertUnprocessable();
        $this->get('/api/install?token=secret-token')->assertOk()->assertSee('Run setup');
    }

    public function test_browser_setup_works_before_sessions_and_cache_tables_exist(): void
    {
        config(['app.install_token' => 'secret-token', 'session.driver' => 'database', 'cache.default' => 'database']);
        Schema::drop('sessions');
        Schema::drop('cache');

        $this->withHeaders(['Referer' => 'http://localhost/'])
            ->postJson('/api/install', ['token' => 'secret-token'] + $this->admin)
            ->assertOk()->assertJsonPath('admin_created', true);
    }

    public function test_attempts_are_throttled(): void
    {
        config(['app.install_token' => 'secret-token']);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/install', ['token' => 'nope'])->assertForbidden();
        }
        $this->postJson('/api/install', ['token' => 'secret-token'] + $this->admin)->assertTooManyRequests();
    }
}
