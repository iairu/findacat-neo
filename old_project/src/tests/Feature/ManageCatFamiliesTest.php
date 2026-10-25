<?php

namespace Tests\Feature;

use App\Cat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageCatFamiliesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cat_can_update_their_sire()
    {
        $cat = factory(Cat::class);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->dontSeeElement('input', ['name' => 'set_sire']);
        $this->click(trans('cat.set_sire'));
        $this->seePageIs(route('cats.show', ['action' => 'set_sire']));
        $this->seeElement('input', ['name' => 'set_sire']);

        $this->submitForm('set_sire_button', [
            'set_sire' => 'Name',
        ]);

        $this->seeInDatabase('cats', [
            'nickname' => 'Name',
        ]);

        $this->assertEquals('Name', $cat->fresh()->sire->nickname);
    }

    /** @test */
    public function cat_can_update_their_dam()
    {
        $cat = factory(Cat::class);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->dontSeeElement('input', ['name' => 'set_dam']);
        $this->click(trans('cat.set_dam'));
        $this->seePageIs(route('cats.show', ['action' => 'set_dam']));
        $this->seeElement('input', ['name' => 'set_dam']);

        $this->submitForm('set_dam_button', [
            'set_dam' => 'Name',
        ]);

        $this->seeInDatabase('cats', [
            'nickname'   => 'Name',
        ]);

        $this->assertEquals('Name', $cat->fresh()->dam->nickname);
    }

    /** @test */
    public function cat_can_add_childrens()
    {
        $cat = factory(Cat::class)->create(['gender_id' => 1]);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click(trans('cat.add_child'));
        $this->seeElement('input', ['name' => 'add_child_name']);
        $this->seeElement('input', ['name' => 'add_child_gender_id']);
        $this->seeElement('select', ['name' => 'add_child_parent_id']);

        $this->submitForm(trans('cat.add_child'), [
            'add_child_name'      => 'Name 1',
            'add_child_gender_id' => 1,
            'add_child_parent_id' => '',
        ]);

        $this->seeInDatabase('cats', [
            'nickname'   => 'Name 1',
            'gender_id'  => 1,
            'sire_id'  => $cat->id,
            'dam_id'  => null,
            'parent_id'  => null,
        ]);
    }

    /** @test */
    public function cat_can_add_childrens_with_parent_id_if_exist()
    {
        $husband = factory(Cat::class)->create(['gender_id' => 1]);
        $wife = factory(Cat::class)->states('female')->create([]);
        $husband->addWife($wife);

        $marriageId = $husband->fresh()->wifes->first()->pivot->id;

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click(trans('cat.add_child'));
        $this->seeElement('input', ['name' => 'add_child_name']);
        $this->seeElement('input', ['name' => 'add_child_gender_id']);
        $this->seeElement('select', ['name' => 'add_child_parent_id']);

        $this->submitForm(trans('cat.add_child'), [
            'add_child_name'      => 'Name 1',
            'add_child_gender_id' => 1,
            'add_child_parent_id' => $marriageId,
        ]);

        $this->seeInDatabase('cats', [
            'nickname'   => 'Name 1',
            'gender_id'  => 1,
            'sire_id'  => $husband->id,
            'dam_id'  => $wife->id,
        ]);
    }

    /** @test */
    public function cat_can_set_wife()
    {
        $cat = factory(Cat::class)->create(['gender_id' => 1]);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click(trans('cat.add_wife'));
        $this->seeElement('input', ['name' => 'set_wife']);

        $this->submitForm('set_wife_button', [
            'set_wife'      => 'Name',
            'marriage_date' => '2010-01-01',
        ]);

        $this->seeInDatabase('cats', [
            'nickname'  => 'Name',
            'gender_id' => 2,
        ]);

        $wife = Cat::where([
            'nickname'  => 'Name',
            'gender_id' => 2,
        ])->first();

        $this->seeInDatabase('couples', [
            'husband_id'    => $cat->id,
            'wife_id'       => $wife->id,
            'marriage_date' => '2010-01-01',
        ]);
    }

    /** @test */
    public function cat_can_set_husband()
    {
        $cat = factory(Cat::class)->create(['gender_id' => 2]);
        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click(trans('cat.add_husband'));
        $this->seeElement('input', ['name' => 'set_husband']);

        $this->submitForm('set_husband_button', [
            'set_husband'   => 'Name',
            'marriage_date' => '2010-03-03',
        ]);

        $this->seeInDatabase('cats', [
            'nickname'   => 'Name',
            'gender_id'  => 1,
        ]);

        $husband = Cat::where([
            'nickname'  => 'Name',
            'gender_id' => 1,
        ])->first();

        $this->seeInDatabase('couples', [
            'husband_id'    => $husband->id,
            'wife_id'       => $cat->id,
            'marriage_date' => '2010-03-03',
        ]);
    }

    /** @test */
    public function cat_can_pick_sire_from_existing_cat()
    {
        $cat = factory(Cat::class);
        $sire = factory(Cat::class)->states('male')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->dontSeeElement('input', ['name' => 'set_sire']);
        $this->click(trans('cat.set_sire'));
        $this->seePageIs(route('cats.show', ['action' => 'set_sire']));
        $this->seeElement('input', ['name' => 'set_sire']);
        $this->seeElement('select', ['name' => 'set_sire_id']);

        $this->submitForm('set_sire_button', [
            'set_sire'    => '',
            'set_sire_id' => $sire->id,
        ]);

        $this->assertEquals($sire->nickname, $cat->fresh()->sire->nickname);
    }

    /** @test */
    public function cat_can_pick_dam_from_existing_cat()
    {
        $cat = factory(Cat::class);
        $dam = factory(Cat::class)->states('female')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->dontSeeElement('input', ['name' => 'set_dam']);
        $this->click(trans('cat.set_dam'));
        $this->seePageIs(route('cats.show', ['action' => 'set_dam']));
        $this->seeElement('input', ['name' => 'set_dam']);
        $this->seeElement('select', ['name' => 'set_dam_id']);

        $this->submitForm('set_dam_button', [
            'set_dam'    => '',
            'set_dam_id' => $dam->id,
        ]);

        $this->assertEquals($dam->nickname, $cat->fresh()->dam->nickname);
    }

    /** @test */
    public function cat_can_pick_wife_from_existing_cat()
    {
        $cat = factory(Cat::class)->create(['gender_id' => 1]);
        $wife = factory(Cat::class)->states('female')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click(trans('cat.add_wife'));
        $this->seeElement('input', ['name' => 'set_wife']);
        $this->seeElement('select', ['name' => 'set_wife_id']);

        $this->submitForm('set_wife_button', [
            'set_wife'      => '',
            'set_wife_id'   => $wife->id,
            'marriage_date' => '2010-01-01',
        ]);

        $this->seeInDatabase('couples', [
            'husband_id'    => $cat->id,
            'wife_id'       => $wife->id,
            'marriage_date' => '2010-01-01',
        ]);
    }

    /** @test */
    public function cat_can_pick_husband_from_existing_cat()
    {
        $cat = factory(Cat::class)->create(['gender_id' => 2]);
        $husband = factory(Cat::class)->states('male')->create();

        $this->visit(route('profile'));
        $this->seePageIs(route('profile'));
        $this->click(trans('cat.add_husband'));
        $this->seeElement('input', ['name' => 'set_husband']);
        $this->seeElement('select', ['name' => 'set_husband_id']);

        $this->submitForm('set_husband_button', [
            'set_husband'    => '',
            'set_husband_id' => $husband->id,
            'marriage_date'  => '2010-03-03',
        ]);

        $this->seeInDatabase('couples', [
            'husband_id'    => $husband->id,
            'wife_id'       => $cat->id,
            'marriage_date' => '2010-03-03',
        ]);
    }

}
