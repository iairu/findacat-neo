<?php

namespace Tests\Feature;

use App\Couple;
use App\Cat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatsDeletionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_delete_a_cat()
    {
        $cat = factory(Cat::class)->create([]);

        $this->visit(route('cats.edit', $cat));
        $this->seeElement('a', ['id' => 'del-cat-'.$cat->id]);

        $this->click('del-cat-'.$cat->id);
        $this->seePageIs(route('cats.edit', [$cat, 'action' => 'delete']));
        $this->see(__('cat.delete_confirm_button'));

        $this->press(__('cat.delete_confirm_button'));

        $this->dontSeeInDatabase('cats', [
            'id' => $cat->id,
        ]);
    }

    /** @test */
    public function user_can_delete_a_cat_the_replace_childs_sire_id()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCat = factory(Cat::class)->states('male')->create([]);
        $oldCatChild = factory(Cat::class)->create(['sire_id' => $oldCat->id]);
        $replacementCat = factory(Cat::class)->states('male')->create([]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'id' => $oldCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'sire_id' => $oldCat->id,
        ]);

        $this->seeInDatabase('cats', [
            'id'        => $oldCatChild->id,
            'sire_id' => $replacementCat->id,
        ]);
    }

    /** @test */
    public function user_can_delete_a_cat_the_replace_childs_dam_id()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCat = factory(Cat::class)->states('female')->create([]);
        $oldCatChild = factory(Cat::class)->create(['dam_id' => $oldCat->id]);
        $replacementCat = factory(Cat::class)->states('female')->create([]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'id' => $oldCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'dam_id' => $oldCat->id,
        ]);

        $this->seeInDatabase('cats', [
            'id'        => $oldCatChild->id,
            'dam_id' => $replacementCat->id,
        ]);
    }

    /** @test */
    public function user_can_delete_a_cat_the_replace_cats_user_id()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCat = factory(Cat::class)->states('male')->create([]);
        $oldCatManagedCat = factory(Cat::class)->create(['user_id' => $oldCat->id]);
        $replacementCat = factory(Cat::class)->states('male')->create([]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'id' => $oldCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            
        ]);

        $this->seeInDatabase('cats', [
            'id'         => $oldCatManagedCat->id,
            'user_id' => $replacementCat->id,
        ]);
    }

    /** @test */
    public function user_can_delete_a_cat_the_replace_couples_husband_id()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCat = factory(Cat::class)->states('male')->create([]);
        $oldCatCouple = factory(Couple::class)->create([
            'husband_id' => $oldCat->id,
        ]);
        $replacementCat = factory(Cat::class)->states('male')->create([]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'id' => $oldCat->id,
        ]);

        $this->dontSeeInDatabase('couples', [
            'husband_id' => $oldCat->id,
        ]);

        $this->seeInDatabase('couples', [
            'id'         => $oldCatCouple->id,
            'husband_id' => $replacementCat->id,
        ]);
    }

    /** @test */
    public function user_can_delete_a_cat_the_replace_couples_wife_id()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCat = factory(Cat::class)->states('female')->create([]);
        $oldCatCouple = factory(Couple::class)->create([
            'wife_id' => $oldCat->id,
        ]);
        $replacementCat = factory(Cat::class)->states('female')->create([]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'id' => $oldCat->id,
        ]);

        $this->dontSeeInDatabase('couples', [
            'wife_id' => $oldCat->id,
        ]);

        $this->seeInDatabase('couples', [
            'id'      => $oldCatCouple->id,
            'wife_id' => $replacementCat->id,
        ]);
    }

    /** @test */
    public function user_can_delete_a_cat_the_replace_couples_user_id()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCat = factory(Cat::class)->states('male')->create([]);
        $oldCoupleManagedCouple = factory(Couple::class)->create([
            
        ]);
        $replacementCat = factory(Cat::class)->states('male')->create([]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', [
            'id' => $oldCat->id,
        ]);

        $this->dontSeeInDatabase('couples', [
            
        ]);

        $this->seeInDatabase('couples', [
            'id'         => $oldCoupleManagedCouple->id,
            'user_id' => $replacementCat->id,
        ]);
    }

    /** @test */
    public function cat_replacement_options_only_available_on_same_gender()
    {
        $cat = factory(Cat::class)->create([]);
        $maleCat = factory(Cat::class)->states('male')->create([]);
        $maleCatChild = factory(Cat::class)->create(['sire_id' => $maleCat->id]);

        $replacementMaleCat = factory(Cat::class)->states('male')->create();
        $femaleCat = factory(Cat::class)->states('female')->create();

        $this->visit(route('cats.edit', [$maleCat, 'action' => 'delete']));

        $this->see(__('cat.replace_delete_text'));
        $this->seeElement('option', ['value' => $replacementMaleCat->id]);
        $this->dontSeeElement('option', ['value' => $femaleCat->id]);
    }

    /** @test */
    public function bugfix_handle_duplicated_couple_on_cat_deletion()
    {
        $cat = factory(Cat::class)->create([]);
        $singleWife = factory(Cat::class)->states('female')->create([]);
        $oldCat = factory(Cat::class)->states('male')->create([]);
        $replacementCat = factory(Cat::class)->states('male')->create([]);
        $oldCatCouple = factory(Couple::class)->create([
            'husband_id' => $oldCat->id,
            'wife_id'    => $singleWife->id,
        ]);
        $duplicatedCouple = factory(Couple::class)->create([
            'husband_id' => $replacementCat->id,
            'wife_id'    => $singleWife->id,
        ]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', ['id' => $oldCat->id]);

        $this->dontSeeInDatabase('couples', ['husband_id' => $oldCat->id]);

        $this->seeInDatabase('couples', [
            'id'         => $oldCatCouple->id,
            'husband_id' => $replacementCat->id,
            'wife_id'    => $singleWife->id,
        ]);
    }

    /** @test */
    public function bugfix_handle_duplicated_couple_on_cat_deletion_with_different_marriages()
    {
        $cat = factory(Cat::class)->create([]);
        $oldCatWife = factory(Cat::class)->states('female')->create([]);
        $replacementCatWife = factory(Cat::class)->states('female')->create([]);
        $oldCat = factory(Cat::class)->states('male')->create([]);
        $replacementCat = factory(Cat::class)->states('male')->create([]);
        $oldCatCouple = factory(Couple::class)->create([
            'husband_id' => $oldCat->id,
            'wife_id'    => $oldCatWife->id,
        ]);
        $newCatCouple = factory(Couple::class)->create([
            'husband_id' => $replacementCat->id,
            'wife_id'    => $replacementCatWife->id,
        ]);

        $this->visit(route('cats.edit', [$oldCat, 'action' => 'delete']));
        $this->see(__('cat.replace_delete_text'));

        $this->submitForm(__('cat.replace_delete_button'), [
            'replacement_cat_id' => $replacementCat->id,
        ]);

        $this->dontSeeInDatabase('cats', ['id' => $oldCat->id]);

        $this->dontSeeInDatabase('couples', ['husband_id' => $oldCat->id]);

        $this->seeInDatabase('couples', [
            'id'         => $oldCatCouple->id,
            'husband_id' => $replacementCat->id,
            'wife_id'    => $oldCatWife->id,
        ]);

        $this->seeInDatabase('couples', [
            'id'         => $newCatCouple->id,
            'husband_id' => $replacementCat->id,
            'wife_id'    => $replacementCatWife->id,
        ]);
    }
}
