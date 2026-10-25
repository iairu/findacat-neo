<?php

namespace App\Services;

use App\Models\Cat;
use Illuminate\Support\Collection;

/** Loads and shapes ancestor trees. Only approved cats are shown; a hidden ancestor ends its branch. */
class Pedigree
{
    public const MAX_GENERATIONS = 6;

    private const COLUMNS = ['id', 'name', 'sex', 'sire_id', 'dam_id', 'born_on', 'died_on', 'breed_code', 'ems_code', 'title_prefix', 'title_suffix', 'photo', 'reg_number'];

    /** @return array<string, mixed> nested tree with `sire` and `dam` keys */
    public function tree(Cat $root, int $generations): array
    {
        $generations = max(1, min($generations, self::MAX_GENERATIONS));
        $cats = [$root->id => $root];
        $level = [$root];

        for ($i = 0; $i < $generations && $level; $i++) {
            $ids = collect($level)->flatMap(fn (Cat $c) => [$c->sire_id, $c->dam_id])
                ->filter()->unique()->reject(fn ($id) => isset($cats[$id]))->values();
            $level = $ids->isEmpty() ? [] : $this->load($ids)->all();
            foreach ($level as $cat) {
                $cats[$cat->id] = $cat;
            }
        }

        return $this->node($root, $cats, $generations);
    }

    /** True when $candidate is $cat itself or one of its ancestors. */
    public function isSelfOrAncestor(string $candidate, string $cat): bool
    {
        $seen = [];
        $pending = [$cat];
        while ($pending) {
            if (in_array($candidate, $pending, true)) {
                return true;
            }
            $seen = array_merge($seen, $pending);
            $pending = Cat::query()->whereIn('id', $pending)->get(['sire_id', 'dam_id'])
                ->flatMap(fn ($c) => [$c->sire_id, $c->dam_id])
                ->filter()->unique()->diff($seen)->values()->all();
        }

        return false;
    }

    /** @return Collection<int, Cat> */
    private function load(Collection $ids): Collection
    {
        return Cat::query()->approved()->whereIn('id', $ids)->get(self::COLUMNS);
    }

    private function node(Cat $cat, array $cats, int $depth): array
    {
        $node = [
            'id' => $cat->id,
            'name' => $cat->name,
            'sex' => $cat->sex,
            'born_on' => $cat->born_on?->toDateString(),
            'died_on' => $cat->died_on?->toDateString(),
            'breed_code' => $cat->breed_code,
            'ems_code' => $cat->ems_code,
            'title_prefix' => $cat->title_prefix,
            'title_suffix' => $cat->title_suffix,
            'photo' => $cat->photo,
            'reg_number' => $cat->reg_number,
            'sire' => null,
            'dam' => null,
        ];

        if ($depth > 0) {
            foreach (['sire', 'dam'] as $role) {
                $parent = $cats[$cat->{$role.'_id'}] ?? null;
                $node[$role] = $parent ? $this->node($parent, $cats, $depth - 1) : null;
            }
        }

        return $node;
    }
}
