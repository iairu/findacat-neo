<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use App\Services\Inbreeding;
use App\Services\Pedigree;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LookupController extends Controller
{
    public function breeds(): array
    {
        return ['data' => Breed::orderBy('code')->get(['id', 'code', 'name'])];
    }

    public function ems(Request $request): array
    {
        $query = EmsColor::query()->orderBy('code');
        if ($request->filled('breed_id')) {
            $query->where('breed_id', $request->query('breed_id'));
        }

        return ['data' => $query->get(['id', 'breed_id', 'code', 'english_name'])];
    }

    public function stats(): array
    {
        $cats = Cat::query()->approved();

        return ['data' => [
            'cats' => (clone $cats)->count(),
            'males' => (clone $cats)->where('sex', Cat::MALE)->count(),
            'females' => (clone $cats)->where('sex', Cat::FEMALE)->count(),
            'living' => (clone $cats)->whereNull('died_on')->count(),
            'deceased' => (clone $cats)->whereNotNull('died_on')->count(),
            'with_photo' => (clone $cats)->whereNotNull('photo')->count(),
        ]];
    }

    /** Cats whose birthday falls within the next 60 days of the given date (default today). */
    public function birthdays(Request $request): array
    {
        $data = $request->validate(['month' => 'nullable|integer|between:1,12', 'day' => 'nullable|integer|between:1,31']);
        $from = now()->startOfDay();
        if (isset($data['month'], $data['day'])) {
            if (! checkdate($data['month'], $data['day'], $from->year)) {
                throw ValidationException::withMessages(['day' => 'This is not a valid date.']);
            }
            $from = $from->setDate($from->year, $data['month'], $data['day']);
        }
        $until = $from->copy()->addDays(60);

        $cats = Cat::query()->approved()->whereNotNull('born_on')->get(['id', 'name', 'born_on'])
            ->map(function (Cat $cat) use ($from, $until) {
                foreach ([$from->year, $until->year] as $year) {
                    if (! checkdate($cat->born_on->month, $cat->born_on->day, $year)) {
                        continue;
                    }
                    $next = $cat->born_on->copy()->setYear($year)->startOfDay();
                    if ($next->betweenIncluded($from, $until)) {
                        return ['id' => $cat->id, 'name' => $cat->name, 'born_on' => $cat->born_on->toDateString(), 'birthday' => $next->toDateString(), 'turns' => $year - $cat->born_on->year];
                    }
                }

                return null;
            })
            ->filter()->sortBy('birthday')->values();

        return ['data' => $cats];
    }

    public function testMating(Request $request, Pedigree $pedigree): array
    {
        $data = $request->validate([
            'sire' => 'required|string|exists:cats,id',
            'dam' => 'required|string|exists:cats,id',
            'generations' => 'nullable|integer|min:1|max:'.Pedigree::MAX_GENERATIONS,
        ]);
        $sire = Cat::query()->approved()->findOrFail($data['sire']);
        $dam = Cat::query()->approved()->findOrFail($data['dam']);
        if ($sire->sex !== Cat::MALE || $dam->sex !== Cat::FEMALE) {
            throw ValidationException::withMessages(['sire' => 'A test mating needs a male sire and a female dam.']);
        }
        $generations = (int) ($data['generations'] ?? 4);

        return ['data' => [
            'inbreeding' => Inbreeding::forCats([$sire->id, $dam->id])->ofMating($sire->id, $dam->id),
            'sire' => $pedigree->tree($sire, $generations - 1),
            'dam' => $pedigree->tree($dam, $generations - 1),
        ]];
    }
}
