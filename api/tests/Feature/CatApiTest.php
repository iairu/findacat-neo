<?php

namespace Tests\Feature;

use App\Models\Cat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatApiTest extends TestCase
{
    use RefreshDatabase;

    private function cat(string $name, string $sex, array $extra = []): Cat
    {
        return Cat::create(['name' => $name, 'sex' => $sex] + $extra);
    }

    private function user(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    public function test_search_filters_and_hides_unapproved_cats(): void
    {
        $this->cat('Whiskers', Cat::MALE, ['ems_code' => 'n']);
        $this->cat('Whisky', Cat::FEMALE);
        $this->cat('Hidden Whisk', Cat::FEMALE, ['moderation_status' => 'rejected']);

        $this->getJson('/api/cats?q=whisk')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/cats?q=Whiskers&exact=1')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/cats?q=whisk&sex=female')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/cats?ems_code=n')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_search_treats_wildcards_literally(): void
    {
        $this->cat('Plain', Cat::MALE);
        $this->getJson('/api/cats?q=%25')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_show_includes_inbreeding_and_kittens(): void
    {
        $a = $this->cat('A', Cat::MALE);
        $b = $this->cat('B', Cat::FEMALE);
        $s1 = $this->cat('S1', Cat::MALE, ['sire_id' => $a->id, 'dam_id' => $b->id]);
        $s2 = $this->cat('S2', Cat::FEMALE, ['sire_id' => $a->id, 'dam_id' => $b->id]);
        $k = $this->cat('K', Cat::FEMALE, ['sire_id' => $s1->id, 'dam_id' => $s2->id]);

        $this->getJson("/api/cats/{$k->id}")->assertOk()
            ->assertJsonPath('data.inbreeding', 0.25)
            ->assertJsonPath('data.sire.name', 'S1')
            ->assertJsonPath('data.can_edit', false);
        $this->getJson("/api/cats/{$s1->id}")->assertJsonPath('data.children.0.name', 'K');
    }

    public function test_unapproved_cat_is_hidden_from_the_public_but_not_the_owner(): void
    {
        $owner = $this->user();
        $cat = $this->cat('Secret', Cat::MALE, ['moderation_status' => 'pending', 'owner_id' => $owner->id]);

        $this->getJson("/api/cats/{$cat->id}")->assertNotFound();
        $this->actingAs($owner, 'sanctum')->getJson("/api/cats/{$cat->id}")->assertOk();
    }

    public function test_pedigree_tree_respects_generations(): void
    {
        $g = $this->cat('G', Cat::MALE);
        $p = $this->cat('P', Cat::MALE, ['sire_id' => $g->id]);
        $c = $this->cat('C', Cat::FEMALE, ['sire_id' => $p->id]);

        $tree = $this->getJson("/api/cats/{$c->id}/pedigree?generations=2")->assertOk()->json('data');
        $this->assertSame('G', $tree['sire']['sire']['name']);

        $tree = $this->getJson("/api/cats/{$c->id}/pedigree?generations=1")->json('data');
        $this->assertSame('P', $tree['sire']['name']);
        $this->assertNull($tree['sire']['sire']);
    }

    public function test_test_mating_returns_the_coefficient(): void
    {
        $a = $this->cat('A', Cat::MALE);
        $b = $this->cat('B', Cat::FEMALE);
        $s = $this->cat('S', Cat::MALE, ['sire_id' => $a->id, 'dam_id' => $b->id]);
        $d = $this->cat('D', Cat::FEMALE, ['sire_id' => $a->id, 'dam_id' => $b->id]);

        $this->getJson("/api/test-mating?sire={$s->id}&dam={$d->id}")->assertOk()
            ->assertJsonPath('data.inbreeding', 0.25);
        $this->getJson("/api/test-mating?sire={$d->id}&dam={$s->id}")->assertStatus(422);
    }

    public function test_birthdays_lists_upcoming_cats(): void
    {
        $this->travelTo('2026-10-25');
        $this->cat('Soon', Cat::MALE, ['born_on' => '2020-11-02']);
        $this->cat('Far', Cat::MALE, ['born_on' => '2020-03-02']);
        $this->cat('Leap', Cat::MALE, ['born_on' => '2020-02-29']);

        $rows = $this->getJson('/api/birthdays')->assertOk()->json('data');
        $this->assertSame(['Soon'], array_column($rows, 'name'));
        $this->assertSame(6, $rows[0]['turns']);
        $this->getJson('/api/birthdays?month=2&day=30')->assertStatus(422);
    }

    public function test_creating_a_cat_needs_a_verified_user(): void
    {
        $payload = ['name' => 'New', 'sex' => 'female'];

        $this->postJson('/api/cats', $payload)->assertUnauthorized();
        $this->actingAs($this->user(['email_verified_at' => null]), 'sanctum')
            ->postJson('/api/cats', $payload)->assertForbidden();

        $user = $this->user();
        $this->actingAs($user, 'sanctum')->postJson('/api/cats', $payload)
            ->assertCreated()->assertJsonPath('data.owner_id', $user->id)
            ->assertJsonPath('data.moderation_status', 'pending');
    }

    public function test_admin_created_cats_are_approved_at_once(): void
    {
        $this->actingAs($this->user(['is_admin' => true]), 'sanctum')
            ->postJson('/api/cats', ['name' => 'Staff pick', 'sex' => 'male'])
            ->assertCreated()->assertJsonPath('data.moderation_status', 'approved');
    }

    public function test_moderation_queue_and_decisions_are_admin_only(): void
    {
        $owner = $this->user();
        $cat = $this->cat('Waiting', Cat::MALE, ['moderation_status' => 'pending', 'owner_id' => $owner->id]);

        $this->actingAs($owner, 'sanctum')->getJson('/api/admin/cats')->assertForbidden();
        $this->actingAs($owner, 'sanctum')->postJson("/api/admin/cats/{$cat->id}/approve")->assertForbidden();
        $this->getJson('/api/cats')->assertJsonCount(0, 'data');
        $this->actingAs($owner, 'sanctum')->getJson('/api/my/cats')->assertJsonCount(1, 'data');

        $admin = $this->user(['is_admin' => true]);
        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/cats')->assertOk()->assertJsonCount(1, 'data');
        $this->postJson("/api/admin/cats/{$cat->id}/approve")->assertOk();
        $this->getJson('/api/cats')->assertJsonCount(1, 'data');
        $this->postJson("/api/admin/cats/{$cat->id}/reject")->assertOk();
        $this->assertSame('rejected', $cat->fresh()->moderation_status);
    }

    public function test_a_pending_cat_cannot_be_used_as_a_parent_by_others(): void
    {
        $pending = $this->cat('Pending', Cat::MALE, ['moderation_status' => 'pending', 'owner_id' => $this->user()->id]);

        $this->actingAs($this->user(), 'sanctum')
            ->postJson('/api/cats', ['name' => 'Kit', 'sex' => 'male', 'sire_id' => $pending->id])
            ->assertStatus(422)->assertJsonValidationErrors('sire_id');
        $this->actingAs($pending->owner, 'sanctum')
            ->postJson('/api/cats', ['name' => 'Kit', 'sex' => 'male', 'sire_id' => $pending->id])
            ->assertCreated();
    }

    public function test_parents_must_have_the_right_sex_and_no_cycles(): void
    {
        $user = $this->user();
        $sire = $this->cat('Sire', Cat::MALE);
        $female = $this->cat('Female', Cat::FEMALE);
        $kitten = $this->cat('Kitten', Cat::FEMALE, ['sire_id' => $sire->id, 'owner_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/cats', ['name' => 'X', 'sex' => 'male', 'sire_id' => $female->id])
            ->assertStatus(422)->assertJsonValidationErrors('sire_id');

        $grown = $this->cat('Grown', Cat::MALE, ['owner_id' => $user->id, 'sire_id' => $sire->id]);
        $kitten->update(['sire_id' => $grown->id]);
        $this->putJson("/api/cats/{$grown->id}", ['sire_id' => $grown->id])->assertStatus(422);
        $sire->update(['owner_id' => $user->id]);
        $this->putJson("/api/cats/{$sire->id}", ['sire_id' => $kitten->id])->assertStatus(422);
    }

    public function test_only_owner_or_admin_can_edit_and_delete(): void
    {
        $owner = $this->user();
        $cat = $this->cat('Mine', Cat::MALE, ['owner_id' => $owner->id]);

        $this->actingAs($this->user(), 'sanctum')->putJson("/api/cats/{$cat->id}", ['name' => 'Stolen'])->assertForbidden();
        $this->actingAs($owner, 'sanctum')->putJson("/api/cats/{$cat->id}", ['name' => 'Renamed'])->assertOk();
        $this->assertSame('Renamed', $cat->fresh()->name);

        $admin = $this->user(['is_admin' => true]);
        $this->actingAs($admin, 'sanctum')->deleteJson("/api/cats/{$cat->id}")->assertNoContent();
        $this->assertNull(Cat::find($cat->id));
    }

    public function test_sex_cannot_change_when_the_cat_has_kittens(): void
    {
        $user = $this->user();
        $sire = $this->cat('Sire', Cat::MALE, ['owner_id' => $user->id]);
        $this->cat('Kitten', Cat::MALE, ['sire_id' => $sire->id]);

        $this->actingAs($user, 'sanctum')->putJson("/api/cats/{$sire->id}", ['sex' => 'female'])
            ->assertStatus(422)->assertJsonValidationErrors('sex');
    }
}
