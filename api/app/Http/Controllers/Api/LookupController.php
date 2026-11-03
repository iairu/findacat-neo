<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use App\Services\EmsLookup;
use App\Services\Inbreeding;
use App\Services\Pedigree;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LookupController extends Controller
{
    private const CATALOGUE_CATS = 12;

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

    /** Every EMS colour with the approved cats that carry its code. */
    public function emsCats(): array
    {
        return ['data' => $this->catalogue('ems_code', EmsColor::query()->get(['code', 'english_name'])->map(fn (EmsColor $row) => [$row->code, $row->english_name]))];
    }

    /** Every breed with the approved cats of that breed. */
    public function breedCats(): array
    {
        return ['data' => $this->catalogue('breed_code', Breed::query()->get(['code', 'name'])->map(fn (Breed $row) => [$row->code, $row->name]))];
    }

    /**
     * Groups approved cats by a code column (matched the way EmsLookup does) and lists every
     * known code even when no cat uses it. Each group carries the total and its first cats by name.
     *
     * @param  Collection<int, array{0: string, 1: ?string}>  $reference  Known [code, name] pairs.
     * @return array<int, array{code: string, name: ?string, total: int, cats: Collection}>
     */
    private function catalogue(string $column, Collection $reference): array
    {
        $cats = Cat::query()->approved()->whereNotNull($column)->where($column, '!=', '')->orderBy('name')
            ->get(['id', 'name', 'sex', 'born_on', 'breed_code', 'ems_code'])
            ->groupBy(fn (Cat $cat) => EmsLookup::normalize($cat->{$column}));

        $known = $reference->filter(fn (array $row) => EmsLookup::normalize($row[0]) !== '')
            ->groupBy(fn (array $row) => EmsLookup::normalize($row[0]));

        return $known->keys()->merge($cats->keys())->unique()
            ->map(function (string $key) use ($known, $cats, $column) {
                $rows = $known->get($key);
                $group = $cats->get($key, collect());

                return [
                    'code' => $rows ? trim($rows->first()[0]) : trim($group->first()->{$column}),
                    'name' => $rows?->map(fn (array $row) => trim((string) $row[1]))->first(fn (string $name) => $name !== ''),
                    'total' => $group->count(),
                    'cats' => $group->take(self::CATALOGUE_CATS)->values(),
                ];
            })
            ->sortBy('code', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
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
