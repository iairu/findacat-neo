<?php

namespace Tests\Feature;

use App\Cat;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $cat;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create test cat with complete data
        $this->cat = Cat::create([
            'full_name' => 'Test Cat',
            'gender_id' => 1,
            'dob' => '2020-01-01',
            'breed' => 'Persian',
            'ems_color' => 'BLK',
        ]);
    }

    /** @test */
    public function home_page_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function search_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('cats.search'));
        $response->assertStatus(200);
        $response->assertSee('Search');
    }

    /** @test */
    public function cat_profile_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('cats.show', $this->cat->id));
        $response->assertStatus(200);
        $response->assertSee($this->cat->full_name);
    }

    /** @test */
    public function cat_chart_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('cats.chart', $this->cat->id));
        $response->assertStatus(200);
    }

    /** @test */
    public function cat_tree_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('cats.tree', ['id' => $this->cat->id, 'gen' => 3]));
        $response->assertStatus(200);
    }

    /** @test */
    public function test_mating_page_loads_successfully()
    {
        $cat2 = Cat::create([
            'full_name' => 'Test Cat 2',
            'gender_id' => 2,
            'dob' => '2020-01-01',
            'breed' => 'Persian',
            'ems_color' => 'BLK',
        ]);

        $response = $this->actingAs($this->user)->get("/test/{$this->cat->id}/{$cat2->id}/3/3");
        $response->assertStatus(200);
    }

    /** @test */
    public function death_page_loads_successfully()
    {
        $this->cat->update([
            'dod' => '2023-01-01',
        ]);

        $response = $this->actingAs($this->user)->get(route('cats.death', $this->cat->id));
        $response->assertStatus(200);
    }

    /** @test */
    public function login_page_loads_successfully()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    /** @test */
    public function register_page_loads_successfully()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Register');
    }

    /** @test */
    public function admin_can_access_register_cat_page()
    {
        $response = $this->actingAs($this->admin)->get(route('register-cat'));
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_register_cat_page()
    {
        $response = $this->actingAs($this->user)->get(route('register-cat'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_backups_page()
    {
        $response = $this->actingAs($this->admin)->get(route('backups.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_backups_page()
    {
        $response = $this->actingAs($this->user)->get(route('backups.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_edit_cat_page()
    {
        $response = $this->actingAs($this->admin)->get(route('cats.edit', $this->cat->id));
        $response->assertStatus(200);
        $response->assertSee($this->cat->full_name);
    }

    /** @test */
    public function non_admin_cannot_access_edit_cat_page()
    {
        $response = $this->actingAs($this->user)->get(route('cats.edit', $this->cat->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function theme_css_variables_are_present()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Check that the enhanced CSS is loaded
        $cssContent = file_get_contents(public_path('css/app.css'));
        $this->assertNotEmpty($cssContent);
    }

    /** @test */
    public function enhanced_tree_css_exists()
    {
        $this->assertFileExists(public_path('css/enhanced-tree.css'));
        $cssContent = file_get_contents(public_path('css/enhanced-tree.css'));
        $this->assertStringContainsString('tree-layout', $cssContent);
    }

    /** @test */
    public function favicon_svg_exists()
    {
        $this->assertFileExists(public_path('images/favicon.svg'));
    }

    /** @test */
    public function layout_includes_theme_toggle()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('theme-toggle', false);
        $response->assertSee('themeToggle', false);
    }

    /** @test */
    public function layout_includes_footer()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('site-footer', false);
    }

    /** @test */
    public function layout_includes_accessibility_skip_link()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('skip-link', false);
        $response->assertSee('Skip to main content');
    }

    /** @test */
    public function page_has_proper_meta_tags()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="viewport"', false);
    }

    /** @test */
    public function navigation_has_enhanced_styling()
    {
        $response = $this->actingAs($this->user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('navbar-default', false);
    }
}
