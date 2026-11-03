<?php

namespace App\Services;

use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds a small, self-contained pedigree for demos and manual testing.
 *
 * Founders are created first and every later generation only points at the generation
 * before it, so the result can never contain an ancestry cycle.
 */
class FakePedigree
{
    public const GENERATIONS = 4;

    public const PER_GENERATION = 6;

    /**
     * @return array{cats: int, breeds: int, ems: int}
     */
    public function seed(): array
    {
        return DB::transaction(function (): array {
            $created = ['cats' => 0, 'breeds' => 0, 'ems' => 0];
            [$breeds, $ems] = $this->lookups($created);

            $fake = fake();
            $sires = collect();
            $dams = collect();
            // Start far enough back that the last generation is a few years old.
            $start = now()->subYears((self::GENERATIONS - 1) * 3 + 3)->startOfYear();

            for ($generation = 0; $generation < self::GENERATIONS; $generation++) {
                $males = collect();
                $females = collect();
                $bornFrom = $start->copy()->addYears($generation * 3);

                for ($i = 0; $i < self::PER_GENERATION; $i++) {
                    $sex = $i % 2 === 0 ? Cat::MALE : Cat::FEMALE;
                    $born = $bornFrom->copy()->addDays($fake->numberBetween(0, 364));

                    $cat = Cat::create([
                        'name' => $fake->firstName(),
                        'sex' => $sex,
                        'sire_id' => $generation === 0 ? null : $sires->random()->id,
                        'dam_id' => $generation === 0 ? null : $dams->random()->id,
                        'born_on' => $born->toDateString(),
                        'title_prefix' => $fake->boolean(15) ? $fake->randomElement(['CH', 'GIC', 'EC']) : null,
                        'title_suffix' => $fake->boolean(15) ? $fake->randomElement(['JW', 'DSM', 'WC']) : null,
                        'breed_code' => $breeds->isEmpty() ? null : $breeds->random()->code,
                        'ems_code' => $ems->isEmpty() ? null : $ems->random()->code,
                        'reg_number' => 'FAKE-'.$fake->numerify('######'),
                        'chip_number' => $fake->boolean(50) ? $fake->numerify('985############') : null,
                        'breeder' => $fake->name(),
                        'owner_name' => $fake->name(),
                        'country_code' => $fake->randomElement(['SK', 'CZ', 'AT', 'DE', 'HU', 'PL']),
                        'notes' => 'Fake pedigree record generated for testing.',
                        'moderation_status' => 'approved',
                    ]);

                    $created['cats']++;
                    ($sex === Cat::MALE ? $males : $females)->push($cat);
                }

                $sires = $males;
                $dams = $females;
            }

            return $created;
        });
    }

    /**
     * Fake cats need something to point their breed and EMS columns at. Only create
     * lookups when the tables are empty, so a real database keeps its own values.
     *
     * @param  array{cats: int, breeds: int, ems: int}  $created
     * @return array{0: Collection<int, Breed>, 1: Collection<int, EmsColor>}
     */
    private function lookups(array &$created): array
    {
        $breeds = Breed::query()->get(['id', 'code']);

        if ($breeds->isEmpty()) {
            foreach ([
                ['PER', 'Persian'],
                ['MCO', 'Maine Coon'],
                ['SIB', 'Siberian'],
                ['BEN', 'Bengal'],
                ['RAG', 'Ragdoll'],
                ['ABY', 'Abyssinian'],
            ] as [$code, $name]) {
                $breeds->push(Breed::create(['code' => $code, 'name' => $name]));
                $created['breeds']++;
            }
        }

        $ems = EmsColor::query()->get(['id', 'code']);

        if ($ems->isEmpty()) {
            $breedId = $breeds->firstWhere('code', 'PER')?->id ?? $breeds->first()?->id;

            foreach ([
                ['n', 'Black'],
                ['a', 'Blue'],
                ['d', 'Red'],
                ['e', 'Cream'],
                ['w 62', 'White, orange eyes'],
                ['ns 22', 'Brown tabby'],
            ] as [$code, $name]) {
                $ems->push(EmsColor::create(['code' => $code, 'english_name' => $name, 'breed_id' => $breedId]));
                $created['ems']++;
            }
        }

        return [$breeds, $ems];
    }
}
