<?php

namespace Tests\Feature;

use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use App\Models\User;
use App\Services\FakePedigree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_non_admins_cannot_reach_admin_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/admin/breeds', ['code' => 'ABC'])->assertForbidden();
        $this->actingAs($user)->getJson('/api/admin/users')->assertForbidden();
        $this->actingAs($user)->postJson('/api/admin/seed/cats')->assertForbidden();
        $this->actingAs($user)->getJson('/api/admin/backup/cats')->assertForbidden();
        $this->actingAs($user)->deleteJson('/api/admin/backup/cats', ['confirm' => 'DELETE'])->assertForbidden();
    }

    public function test_admin_seeds_a_fake_pedigree(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/admin/seed/cats')
            ->assertCreated()
            ->assertJsonPath('data.cats', FakePedigree::GENERATIONS * FakePedigree::PER_GENERATION)
            ->assertJsonPath('data.breeds', 6)
            ->assertJsonPath('data.ems', 6);

        $cats = Cat::all()->keyBy('id');

        $this->assertCount(FakePedigree::GENERATIONS * FakePedigree::PER_GENERATION, $cats);
        $this->assertSame(FakePedigree::PER_GENERATION, $cats->whereNull('sire_id')->count());
        $this->assertSame(6, Breed::count());
        $this->assertSame(6, EmsColor::count());

        foreach ($cats as $cat) {
            $this->assertSame('approved', $cat->moderation_status);
            $this->assertStringStartsWith('https://placecats.com/', $cat->photo);
            $this->assertNotEmpty($cat->print_name_1);
            $this->assertNotEmpty($cat->print_name_2);
            $this->assertNotEmpty($cat->reg_number);
            $this->assertNotEmpty($cat->chip_number);
            $this->assertNotEmpty($cat->breeder);
            $this->assertNotEmpty($cat->owner_name);
            $this->assertNotEmpty($cat->country);
            $this->assertNotEmpty($cat->country_of_origin);
            $this->assertNotEmpty($cat->notes);
            $this->assertNotEmpty($cat->metadata['doo'] ?? null);

            if ($cat->genetic_tests !== null) {
                $this->assertNotEmpty($cat->genetic_tests_file);
            }

            if ($cat->died_on !== null) {
                $this->assertTrue($cat->born_on->lt($cat->died_on));
                $this->assertArrayHasKey('cemetery', $cat->metadata);
            }

            if ($cat->sire_id !== null) {
                $sire = $cats[$cat->sire_id];
                $this->assertSame(Cat::MALE, $sire->sex);
                $this->assertTrue($sire->born_on->lt($cat->born_on));
            }

            if ($cat->dam_id !== null) {
                $dam = $cats[$cat->dam_id];
                $this->assertSame(Cat::FEMALE, $dam->sex);
                $this->assertTrue($dam->born_on->lt($cat->born_on));
            }
        }

        // A second run adds more cats but reuses the breeds and colours that now exist.
        $this->actingAs($admin)->postJson('/api/admin/seed/cats')
            ->assertCreated()
            ->assertJsonPath('data.cats', FakePedigree::GENERATIONS * FakePedigree::PER_GENERATION)
            ->assertJsonPath('data.breeds', 0)
            ->assertJsonPath('data.ems', 0);
        $this->assertSame(6, Breed::count());
        $this->assertSame(6, EmsColor::count());
    }

    public function test_admin_manages_breeds_and_ems_colours(): void
    {
        $admin = $this->admin();

        $id = $this->actingAs($admin)->postJson('/api/admin/breeds', ['code' => 'ABC', 'name' => 'Abyssinian'])
            ->assertCreated()->json('data.id');
        $this->actingAs($admin)->postJson('/api/admin/breeds', ['code' => 'ABC'])->assertUnprocessable();
        $this->actingAs($admin)->putJson("/api/admin/breeds/{$id}", ['code' => 'ABC', 'name' => 'Aby'])
            ->assertOk()->assertJsonPath('data.name', 'Aby');

        $emsId = $this->actingAs($admin)->postJson('/api/admin/ems', ['code' => 'n', 'english_name' => 'Black', 'breed_id' => $id])
            ->assertCreated()->json('data.id');
        $this->actingAs($admin)->deleteJson("/api/admin/ems/{$emsId}")->assertNoContent();
        $this->actingAs($admin)->deleteJson("/api/admin/breeds/{$id}")->assertNoContent();

        $this->assertSame(0, Breed::count() + EmsColor::count());
    }

    public function test_admin_promotes_users_but_cannot_demote_themselves(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create();

        $this->actingAs($admin)->patchJson("/api/admin/users/{$other->id}", ['is_admin' => true])->assertOk();
        $this->assertTrue($other->fresh()->is_admin);

        $this->actingAs($admin)->patchJson("/api/admin/users/{$admin->id}", ['is_admin' => false])->assertUnprocessable();
        $this->actingAs($admin)->getJson('/api/admin/users?q='.urlencode($other->email))->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_cats_survive_an_export_clear_import_round_trip(): void
    {
        $admin = $this->admin();
        $sire = Cat::create(['name' => 'Sire', 'sex' => Cat::MALE, 'born_on' => '2015-04-01']);
        $dam = Cat::create(['name' => 'Dam, "The"', 'sex' => Cat::FEMALE]);
        Cat::create([
            'name' => 'Kitten', 'sex' => Cat::FEMALE, 'sire_id' => $sire->id, 'dam_id' => $dam->id,
            'owner_id' => $admin->id, 'moderation_status' => 'pending', 'ems_code' => 'n',
        ]);

        $csv = $this->actingAs($admin)->get('/api/admin/backup/cats')->assertOk()->streamedContent();
        $this->assertStringContainsString('"Dam, ""The"""', $csv);

        $this->actingAs($admin)->deleteJson('/api/admin/backup/cats', ['confirm' => 'nope'])->assertUnprocessable();
        $this->actingAs($admin)->deleteJson('/api/admin/backup/cats', ['confirm' => 'DELETE'])->assertNoContent();
        $this->assertSame(0, Cat::count());

        $file = UploadedFile::fake()->createWithContent('cats.csv', $csv);
        $this->actingAs($admin)->post('/api/admin/backup/cats', ['file' => $file], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('data.counts.cats', 3);

        $kitten = Cat::where('name', 'Kitten')->firstOrFail();
        $this->assertSame($sire->id, $kitten->sire_id);
        $this->assertSame($dam->id, $kitten->dam_id);
        $this->assertSame($admin->id, $kitten->owner_id);
        $this->assertSame('pending', $kitten->moderation_status);
        $this->assertSame('2015-04-01', Cat::find($sire->id)->born_on->toDateString());
        $this->assertSame('Dam, "The"', Cat::find($dam->id)->name);
    }

    public function test_breeds_and_ems_export_and_unknown_tables_404(): void
    {
        $admin = $this->admin();
        $breed = Breed::create(['code' => 'ABC', 'name' => 'Abyssinian']);
        EmsColor::create(['code' => 'n', 'english_name' => 'Black', 'breed_id' => $breed->id]);

        $this->assertStringContainsString('ABC', $this->actingAs($admin)->get('/api/admin/backup/breeds')->assertOk()->streamedContent());
        $this->assertStringContainsString('Black', $this->actingAs($admin)->get('/api/admin/backup/ems')->assertOk()->streamedContent());
        $this->actingAs($admin)->get('/api/admin/backup/users')->assertNotFound();
    }
}
