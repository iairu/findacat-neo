<?php

namespace App\Services;

use App\Models\Cat;
use InvalidArgumentException;

/**
 * Wright's coefficient of inbreeding, F(X) = sum over common ancestors A of
 * (1/2)^(n1+n2+1) * (1 + F(A)).
 *
 * Evaluated through the equivalent kinship recursion: F(X) is the kinship of
 * X's parents, and the kinship of two animals is the average of the kinship of
 * the younger one's parents with the other animal. Kinship of an animal with
 * itself is (1 + F)/2. Unknown parents count as unrelated founders.
 */
class Inbreeding
{
    /** @var array<string, array{0: ?string, 1: ?string}> id => [sire, dam] */
    private array $parents;

    /** @var array<string, int> */
    private array $depth = [];

    /** @var array<string, float> */
    private array $kinship = [];

    /** @param array<string, array{0: ?string, 1: ?string}> $parents */
    public function __construct(array $parents)
    {
        $this->parents = $parents;
    }

    /** Build a calculator holding the pedigrees of the given cats, loading ancestors from the database. */
    public static function forCats(array $ids): self
    {
        $parents = [];
        $pending = array_values(array_unique(array_filter($ids)));

        while ($pending) {
            $rows = Cat::query()->whereIn('id', $pending)->get(['id', 'sire_id', 'dam_id']);
            $next = [];
            foreach ($rows as $row) {
                $parents[$row->id] = [$row->sire_id, $row->dam_id];
                foreach ([$row->sire_id, $row->dam_id] as $parent) {
                    if ($parent !== null && ! isset($parents[$parent])) {
                        $next[$parent] = $parent;
                    }
                }
            }
            $pending = array_values($next);
        }

        return new self($parents);
    }

    /** Coefficient of inbreeding of a known cat, from 0 to 1. */
    public function coefficient(string $id): float
    {
        [$sire, $dam] = $this->parents[$id] ?? [null, null];

        return $this->between($sire, $dam);
    }

    /** Coefficient of a hypothetical offspring of the two cats (a test mating). */
    public function ofMating(?string $sire, ?string $dam): float
    {
        return $this->between($sire, $dam);
    }

    /** Coefficient of relationship-based kinship between two cats; unknown (null) is unrelated. */
    public function between(?string $a, ?string $b): float
    {
        if ($a === null || $b === null) {
            return 0.0;
        }

        if ($a === $b) {
            return (1 + $this->coefficient($a)) / 2;
        }

        $key = strcmp($a, $b) < 0 ? $a.'|'.$b : $b.'|'.$a;
        if (isset($this->kinship[$key])) {
            return $this->kinship[$key];
        }

        // Expand the younger animal; an ancestor can never be younger than its descendant.
        if ($this->depth($a) < $this->depth($b)) {
            [$a, $b] = [$b, $a];
        }
        [$sire, $dam] = $this->parents[$a] ?? [null, null];

        return $this->kinship[$key] = ($this->between($sire, $b) + $this->between($dam, $b)) / 2;
    }

    /** Longest line of known ancestors above the cat; founders are 0. */
    private function depth(string $id, array $trail = []): int
    {
        if (isset($this->depth[$id])) {
            return $this->depth[$id];
        }
        if (isset($trail[$id])) {
            throw new InvalidArgumentException("Cat {$id} is its own ancestor.");
        }
        $trail[$id] = true;

        $best = 0;
        foreach ($this->parents[$id] ?? [] as $parent) {
            if ($parent !== null) {
                $best = max($best, $this->depth($parent, $trail) + 1);
            }
        }

        return $this->depth[$id] = $best;
    }
}
