<?php

namespace App\Services;

use App\Models\EmsColor;
use Illuminate\Support\Collection;

/**
 * Resolves a cat's EMS code to the colour name stored in the ems_colors table.
 *
 * Matching is exact: the code is compared after trimming, lower-casing and collapsing
 * white space ("NS  22" equals "ns 22"), never by prefix or substring ("n" is not "ns 22").
 * When several rows share a code, the row of the cat's own breed wins; otherwise the
 * name is used only if every row agrees on it. An ambiguous code has no name.
 */
class EmsLookup
{
    public static function normalize(?string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', (string) $value)));
    }

    /** @var Collection<string, Collection<int, EmsColor>>|null */
    private ?Collection $byCode = null;

    /** @param iterable<object> $cats Cats (models) get an `ems_name` attribute: the resolved name, or null. */
    public function annotate(iterable $cats): void
    {
        $this->byCode = null;
        foreach ($cats as $cat) {
            $cat->ems_name = $this->nameFor($cat->ems_code, $cat->breed_code);
        }
    }

    public function nameFor(?string $code, ?string $breedCode = null): ?string
    {
        $key = self::normalize($code);
        if ($key === '') {
            return null;
        }
        $this->byCode ??= EmsColor::query()->with('breed:id,code')->whereNotNull('english_name')->get(['id', 'breed_id', 'code', 'english_name'])
            ->filter(fn (EmsColor $row) => trim($row->english_name) !== '')
            ->groupBy(fn (EmsColor $row) => self::normalize($row->code));

        $rows = $this->byCode->get($key);
        if (! $rows) {
            return null;
        }
        $breed = self::normalize($breedCode);
        if ($breed !== '' && ($own = $rows->first(fn (EmsColor $row) => self::normalize($row->breed?->code) === $breed))) {
            return trim($own->english_name);
        }

        return $rows->map(fn (EmsColor $row) => self::normalize($row->english_name))->unique()->count() === 1
            ? trim($rows->first()->english_name)
            : null;
    }
}
