<?php

namespace App\Jobs\Cats;

use App\Couple;
use App\Cat;
use Illuminate\Support\Facades\DB;

class DeleteAndReplaceCat
{
    public $cat;

    public $replacementCatId;

    public function __construct(Cat $cat, string $replacementCatId)
    {
        $this->cat = $cat;
        $this->replacementCatId = $replacementCatId;
    }

    public function handle()
    {
        $cat = $this->cat;
        $replacementCatId = $this->replacementCatId;

        DB::beginTransaction();
        $this->replaceCatOnCatsTable($cat->id, $replacementCatId);
        $this->removeDuplicatedCouples($cat->id, $replacementCatId);
        $this->replaceCatOnCouplesTable($cat->id, $replacementCatId);
        $cat->delete();
        DB::commit();
    }

    private function replaceCatOnCouplesTable($oldCatId, $replacementCatId)
    {
        DB::table('couples')->where('husband_id', $oldCatId)->update([
            'husband_id' => $replacementCatId,
        ]);
        DB::table('couples')->where('wife_id', $oldCatId)->update([
            'wife_id' => $replacementCatId,
        ]);
    }

    private function removeDuplicatedCouples(string $oldCatId, string $replacementCatId)
    {
        $oldCat = Cat::find($oldCatId);
        $replacementCat = Cat::find($replacementCatId);

        if ($replacementCat->gender_id == 1) {
            $replacementCatCouples = Couple::where('husband_id', $replacementCatId)->get();
        } else {
            $replacementCatCouples = Couple::where('wife_id', $replacementCatId)->get();
        }
        if ($oldCat->gender_id == 1) {
            $oldCatCouples = Couple::where('husband_id', $oldCatId)->get();
        } else {
            $oldCatCouples = Couple::where('wife_id', $oldCatId)->get();
        }

        $couplesArray = [];
        foreach ($replacementCatCouples as $replacementCatCouple) {
            $couplesArray[$replacementCatCouple->id] = $replacementCatCouple->husband_id.'_'.$replacementCatCouple->wife_id;
        }
        foreach ($oldCatCouples as $oldCatCouple) {
            $couplesArray[$oldCatCouple->id] = $oldCatCouple->husband_id.'_'.$oldCatCouple->wife_id;
        }
        $couplesArray = collect($couplesArray);
        $deletableCouples = [];
        if ($oldCat->gender_id == 1) {
            foreach ($oldCatCouples as $oldCatCouple) {
                $deletableCouples[] = $couplesArray->search($replacementCatId.'_'.$oldCatCouple->wife_id);
            }
        } else {
            foreach ($oldCatCouples as $oldCatCouple) {
                $deletableCouples[] = $couplesArray->search($oldCatCouple->husband_id.'_'.$replacementCatId);
            }
        }

        if ($deletableCouples) {
            Couple::whereIn('id', $deletableCouples)->delete();
        }
    }

    private function replaceCatonCatsTable(string $oldCatId, string $replacementCatId)
    {
        foreach (['sire_id', 'dam_id'] as $field) {
            DB::table('cats')->where($field, $oldCatId)->update([
                $field => $replacementCatId,
            ]);
        }
    }
}
