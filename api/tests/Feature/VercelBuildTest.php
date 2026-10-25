<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VercelBuildTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = tempnam(sys_get_temp_dir(), 'secrets');
        config(['app.key' => null, 'app.install_token' => null, 'app.url' => 'https://cats.example']);
    }

    protected function tearDown(): void
    {
        @unlink($this->output);
        parent::tearDown();
    }

    /** @return array<string, string> */
    private function build(): array
    {
        $this->artisan('vercel:build', ['--output' => $this->output])->assertSuccessful();

        return require $this->output;
    }

    public function test_generates_secrets_once_and_reuses_them(): void
    {
        $first = $this->build();

        $this->assertStringStartsWith('base64:', $first['APP_KEY']);
        $this->assertSame(32, strlen(base64_decode(substr($first['APP_KEY'], 7))));
        $this->assertSame(40, strlen($first['INSTALL_TOKEN']));
        $this->assertSame('true', $first['INSTALL_FIRST_RUN_ONLY']);
        $this->assertSame($first, $this->build());
    }

    public function test_prints_the_setup_link_until_an_admin_exists(): void
    {
        $this->artisan('vercel:build', ['--output' => $this->output])
            ->expectsOutputToContain('https://cats.example/api/install?token=')
            ->assertSuccessful();

        User::factory()->create(['is_admin' => true]);

        $this->artisan('vercel:build', ['--output' => $this->output])
            ->doesntExpectOutputToContain('/api/install')
            ->assertSuccessful();
        $this->assertArrayNotHasKey('INSTALL_TOKEN', require $this->output);
    }

    public function test_project_variables_take_precedence(): void
    {
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32)), 'app.install_token' => 'own-token']);

        $this->assertSame([], $this->build());
        $this->assertDatabaseCount('app_secrets', 0);
    }

    public function test_generated_setup_token_stops_working_once_an_admin_exists(): void
    {
        config(['app.install_token' => 'generated', 'app.install_first_run_only' => true, 'cache.stores.file' => ['driver' => 'array']]);

        $this->get('/api/install?token=generated')->assertOk();

        User::factory()->create(['is_admin' => true]);

        $this->get('/api/install?token=generated')->assertNotFound();
        $this->postJson('/api/install', ['token' => 'generated'])->assertNotFound();
    }
}
