<?php

namespace App\Legacy;

use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Loads rows exported by the old Laravel 8 app (cats, breeds, EMS colours) into the new schema.
 *
 * Legacy ids are kept, so running an import again updates rows instead of duplicating them.
 * The old app used one "Unknown Origin" cat as the parent of every cat with unknown parents,
 * itself parented to itself; those placeholders are dropped and the links become null.
 */
final class LegacyImporter
{
    /** Legacy cats column => new cats column. Columns not listed here are handled in code. */
    public const CAT_COLUMNS = [
        'full_name' => 'name',
        'titles_before_name' => 'title_prefix',
        'titles_after_name' => 'title_suffix',
        'alternative_name' => 'alt_name',
        'print_name_r1' => 'print_name_1',
        'print_name_r2' => 'print_name_2',
        'breed' => 'breed_code',
        'ems_color' => 'ems_code',
        'chip_number' => 'chip_number',
        'genetic_tests' => 'genetic_tests',
        'genetic_tests_file' => 'genetic_tests_file',
        'original_reg_num' => 'reg_number',
        'last_reg_num' => 'reg_number_last',
        'reg_num_2' => 'reg_number_2',
        'reg_num_3' => 'reg_number_3',
        'breeding_station' => 'breeding_station',
        'breeder' => 'breeder',
        'current_owner' => 'owner_name',
        'country_code' => 'country_code',
        'country_of_origin' => 'country_of_origin',
        'country' => 'country',
        'notes' => 'notes',
        'ownership_notes' => 'ownership_notes',
        'personal_info' => 'personal_info',
        'photo' => 'photo',
        'vet_confirmation' => 'vet_confirmation',
    ];

    /** Legacy value meaning "date not known". */
    private const UNKNOWN_DATE = '1111-11-11';

    /**
     * @param  list<array<string, string|null>>  $rows
     */
    public function importBreeds(array $rows): ImportReport
    {
        $report = new ImportReport;
        $now = now();
        $records = [];
        foreach ($rows as $i => $row) {
            $id = self::text($row['id'] ?? null);
            $code = self::text($row['breed'] ?? null);
            if ($id === null || $code === null) {
                $report->warn('Breed row '.($i + 2).' has no id or code; skipped.');

                continue;
            }
            $records[] = ['id' => $id, 'code' => $code, 'name' => self::text($row['name'] ?? null), 'created_at' => $now, 'updated_at' => $now];
        }

        foreach (array_chunk($records, 200) as $chunk) {
            Breed::query()->upsert($chunk, ['id'], ['code', 'name', 'updated_at']);
        }
        $report->count('breeds', count($records));

        return $report;
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     */
    public function importEms(array $rows): ImportReport
    {
        $report = new ImportReport;
        $now = now();
        $breedIds = array_flip(Breed::query()->pluck('id')->all());
        $records = [];
        foreach ($rows as $i => $row) {
            $id = self::text($row['id'] ?? null);
            $code = self::text($row['ems'] ?? null);
            if ($id === null || $code === null) {
                $report->warn('EMS row '.($i + 2).' has no id or code; skipped.');

                continue;
            }
            $breedId = self::text($row['breed_id'] ?? null);
            if ($breedId !== null && ! isset($breedIds[$breedId])) {
                $report->warn("EMS colour {$code} refers to unknown breed {$breedId}; breed link left empty.");
                $breedId = null;
            }
            $records[] = [
                'id' => $id, 'breed_id' => $breedId, 'code' => $code,
                'english_name' => self::text($row['english'] ?? null),
                'created_at' => $now, 'updated_at' => $now,
            ];
        }

        foreach (array_chunk($records, 200) as $chunk) {
            EmsColor::query()->upsert($chunk, ['id'], ['breed_id', 'code', 'english_name', 'updated_at']);
        }
        $report->count('ems_colors', count($records));

        return $report;
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     */
    public function importCats(array $rows): ImportReport
    {
        $report = new ImportReport;
        $now = now();

        $placeholders = [];
        foreach ($rows as $row) {
            $id = self::text($row['id'] ?? null);
            if ($id !== null && (self::text($row['sire_id'] ?? null) === $id || self::text($row['dam_id'] ?? null) === $id)) {
                $placeholders[$id] = true;
            }
        }

        // Exports from this app carry the owner and moderation status; legacy exports do not, and must not reset them.
        $hasOwnership = $rows !== [] && array_key_exists('owner_id', $rows[0]) && array_key_exists('moderation_status', $rows[0]);
        $userIds = $hasOwnership ? array_flip(User::query()->pluck('id')->all()) : [];

        $records = [];
        $parents = [];
        foreach ($rows as $i => $row) {
            $id = self::text($row['id'] ?? null);
            if ($id === null) {
                $report->warn('Cat row '.($i + 2).' has no id; skipped.');

                continue;
            }
            if (isset($placeholders[$id])) {
                $report->count('placeholders_dropped');

                continue;
            }
            $name = self::text($row['full_name'] ?? null);
            if ($name === null) {
                $report->warn("Cat {$id} has no name; skipped.");

                continue;
            }

            $record = ['id' => $id, 'sex' => $this->sex($row['gender_id'] ?? null, $name, $report)];
            foreach (self::CAT_COLUMNS as $legacy => $new) {
                $record[$new] = self::text($row[$legacy] ?? null);
            }
            $record['born_on'] = $this->date($row['dob'] ?? null, "{$name} date of birth", $report);
            $record['died_on'] = $this->date($row['dod'] ?? null, "{$name} date of death", $report);
            $doo = self::text($row['doo'] ?? null);
            $record['metadata'] = $doo === null ? null : json_encode(['doo' => $doo]);
            if ($hasOwnership) {
                $owner = self::text($row['owner_id'] ?? null);
                $status = self::text($row['moderation_status'] ?? null);
                $record['owner_id'] = $owner !== null && isset($userIds[$owner]) ? $owner : null;
                $record['moderation_status'] = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'approved';
            }
            $record['created_at'] = $now;
            $record['updated_at'] = $now;

            $records[$id] = $record;
            $parents[$id] = [
                'sire_id' => self::text($row['sire_id'] ?? null),
                'dam_id' => self::text($row['dam_id'] ?? null),
            ];
        }

        // Parents may already exist from an earlier import.
        $known = $records + array_flip(Cat::query()->whereIn('id', $this->referencedIds($parents))->pluck('id')->all());
        $parents = $this->resolveParents($parents, $records, $known, $placeholders, $report);
        $this->dropCycles($parents, $records, $report);

        DB::transaction(function () use ($records, $parents) {
            foreach (array_chunk($records, 200) as $chunk) {
                $update = array_diff(array_keys($chunk[array_key_first($chunk)]), ['id', 'created_at']);
                Cat::query()->upsert(array_values($chunk), ['id'], array_values($update));
            }
            foreach ($parents as $id => $links) {
                Cat::query()->whereKey($id)->update($links);
            }
        });

        $report->count('cats', count($records));

        return $report;
    }

    /** Trim; legacy exports use '' and '-' for "nothing". */
    private static function text(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || $value === '-' ? null : $value;
    }

    private function sex(?string $genderId, string $name, ImportReport $report): ?string
    {
        $sex = match (self::text($genderId)) {
            '1' => Cat::MALE,
            '2' => Cat::FEMALE,
            default => null,
        };
        if ($sex === null) {
            $report->warn("{$name} has an unknown gender id; sex left empty.");
        }

        return $sex;
    }

    private function date(?string $value, string $label, ImportReport $report): ?string
    {
        $value = self::text($value);
        if ($value === null || $value === self::UNKNOWN_DATE) {
            return null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return $value;
        }
        $report->warn("{$label} '{$value}' is not a valid date; left empty.");

        return null;
    }

    /**
     * @param  array<string, array{sire_id: ?string, dam_id: ?string}>  $parents
     * @return list<string>
     */
    private function referencedIds(array $parents): array
    {
        $ids = [];
        foreach ($parents as $links) {
            foreach ($links as $id) {
                if ($id !== null) {
                    $ids[$id] = true;
                }
            }
        }

        return array_keys($ids);
    }

    /**
     * Turn raw parent references into valid links: placeholders and unknown ids become null.
     *
     * @param  array<string, array{sire_id: ?string, dam_id: ?string}>  $parents
     * @param  array<string, array<string, mixed>>  $records
     * @param  array<string, mixed>  $known
     * @param  array<string, true>  $placeholders
     * @return array<string, array{sire_id: ?string, dam_id: ?string}>
     */
    private function resolveParents(array $parents, array $records, array $known, array $placeholders, ImportReport $report): array
    {
        $wanted = ['sire_id' => Cat::MALE, 'dam_id' => Cat::FEMALE];
        foreach ($parents as $id => $links) {
            foreach ($links as $column => $parentId) {
                if ($parentId === null || isset($placeholders[$parentId])) {
                    $parents[$id][$column] = null;

                    continue;
                }
                if (! array_key_exists($parentId, $known)) {
                    $report->warn("{$records[$id]['name']} ({$id}) refers to unknown {$column} {$parentId}; link left empty.");
                    $parents[$id][$column] = null;

                    continue;
                }
                $sex = $records[$parentId]['sex'] ?? null;
                if ($sex !== null && $sex !== $wanted[$column]) {
                    $report->warn("{$records[$id]['name']} ({$id}) has a {$sex} cat as {$column}; link kept.");
                }
            }
        }

        return $parents;
    }

    /**
     * Remove links that would make a cat its own ancestor, which would break pedigree walks.
     *
     * @param  array<string, array{sire_id: ?string, dam_id: ?string}>  $parents
     * @param  array<string, array<string, mixed>>  $records
     */
    private function dropCycles(array &$parents, array $records, ImportReport $report): void
    {
        $state = []; // absent = unvisited, 1 = on the current path, 2 = finished
        $visit = function (string $id) use (&$visit, &$state, &$parents, $records, $report): void {
            $state[$id] = 1;
            foreach (['sire_id', 'dam_id'] as $column) {
                $parentId = $parents[$id][$column] ?? null;
                if ($parentId === null || ! isset($parents[$parentId])) {
                    continue; // no parent, or an already-stored cat outside this import
                }
                if (($state[$parentId] ?? 0) === 1) {
                    $report->warn("{$records[$id]['name']} ({$id}) would be its own ancestor through {$column} {$parentId}; link dropped.");
                    $parents[$id][$column] = null;
                } elseif (! isset($state[$parentId])) {
                    $visit($parentId);
                }
            }
            $state[$id] = 2;
        };
        foreach (array_keys($parents) as $id) {
            if (! isset($state[$id])) {
                $visit($id);
            }
        }
    }
}
