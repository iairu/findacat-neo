<?php

namespace App\Legacy;

use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use Closure;

/**
 * Writes cats, breeds and EMS colours as CSV in the old Find A Cat layout, so a backup can be
 * restored by {@see LegacyImporter} and old tools keep working. Cats carry two extra columns,
 * `owner_id` and `moderation_status`, which the importer restores when present.
 */
final class LegacyExporter
{
    public const TABLES = ['cats', 'breeds', 'ems'];

    /**
     * @return Closure(): void
     */
    public function stream(string $table): Closure
    {
        return function () use ($table): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            match ($table) {
                'cats' => $this->cats($out),
                'breeds' => $this->breeds($out),
                'ems' => $this->ems($out),
            };
            fclose($out);
        };
    }

    /** @param resource $out */
    private function cats($out): void
    {
        $columns = self::catsHeader();
        fputcsv($out, $columns, escape: '');

        foreach (Cat::query()->lazyById(500) as $cat) {
            $row = [
                'id' => $cat->id,
                'full_name' => $cat->name,
                'gender_id' => match ($cat->sex) {
                    Cat::MALE => '1',
                    Cat::FEMALE => '2',
                    default => '',
                },
                'sire_id' => $cat->sire_id,
                'dam_id' => $cat->dam_id,
                'dob' => $cat->born_on?->toDateString(),
                'dod' => $cat->died_on?->toDateString(),
                'doo' => $cat->metadata['doo'] ?? null,
                'owner_id' => $cat->owner_id,
                'moderation_status' => $cat->moderation_status,
            ];
            foreach (LegacyImporter::CAT_COLUMNS as $legacy => $new) {
                $row[$legacy] = $cat->{$new};
            }
            fputcsv($out, array_map(fn (string $column) => $row[$column] ?? '', $columns), escape: '');
        }
    }

    /** @param resource $out */
    private function breeds($out): void
    {
        fputcsv($out, ['id', 'breed', 'name'], escape: '');
        foreach (Breed::query()->orderBy('code')->lazyById(500) as $breed) {
            fputcsv($out, [$breed->id, $breed->code, $breed->name ?? ''], escape: '');
        }
    }

    /** @param resource $out */
    private function ems($out): void
    {
        fputcsv($out, ['id', 'breed_id', 'ems', 'english'], escape: '');
        foreach (EmsColor::query()->orderBy('code')->lazyById(500) as $color) {
            fputcsv($out, [$color->id, $color->breed_id ?? '', $color->code, $color->english_name ?? ''], escape: '');
        }
    }

    /** @return list<string> */
    private static function catsHeader(): array
    {
        return [
            'id', 'full_name', 'gender_id', 'sire_id', 'dam_id', 'dob', 'dod', 'doo',
            ...array_keys(LegacyImporter::CAT_COLUMNS),
            'owner_id', 'moderation_status',
        ];
    }
}
