<?php

namespace App\Services;

use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds a small, self-contained pedigree for demos and manual testing.
 *
 * Founders are created first and every later generation only points at the generation
 * before it, so the result can never contain an ancestry cycle. Every column that the
 * API exposes gets realistic filler, including a placeholder cat photo.
 */
class FakePedigree
{
    public const GENERATIONS = 4;

    public const PER_GENERATION = 6;

    private const CATTERIES = ['Amberwhisker', 'Velvet Paw', 'Moonlynx', 'Silver Purr', 'Willow Song', 'Sunny Side'];

    private const COUNTRIES = [
        'SK' => 'Slovakia',
        'CZ' => 'Czechia',
        'AT' => 'Austria',
        'DE' => 'Germany',
        'HU' => 'Hungary',
        'PL' => 'Poland',
    ];

    /** Cat names accepted by placecats.com, so each record gets a real cat photo. */
    private const PHOTOS = ['neo', 'millie', 'bella', 'poppy', 'louie'];

    private const TEST_RESULTS = [
        'HCM: clear (2024), PKD: clear',
        'HCM: clear, FeLV/FIV: negative',
        'PKD: clear, FIV: negative',
        'HCM: equivocal, recheck next year',
        'DNA: PKD clear, HCM clear',
    ];

    private const PERSONAL_NOTES = [
        'Indoor cat, vaccinated yearly and microchipped.',
        'Lives with children and two other cats.',
        'Shy with strangers, very affectionate at home.',
        'Neutered pet, no breeding plans.',
        'Shows occasionally; travels well.',
    ];

    private const VET_NOTES = [
        'Pedigree and health record confirmed by the vet.',
        'HCM screening done, report on file.',
        'Vaccination card checked by the vet.',
        'Chip number verified at the clinic.',
    ];

    /**
     * @return array{cats: int, breeds: int, ems: int}
     */
    public function seed(): array
    {
        return DB::transaction(function (): array {
            $created = ['cats' => 0, 'breeds' => 0, 'ems' => 0];
            [$breeds, $ems] = $this->lookups($created);

            $fake = fake();
            $batch = strtoupper(Str::random(5));
            $sequence = 0;
            $sires = collect();
            $dams = collect();
            $generations = [];
            $lastChildBorn = [];
            // Start far enough back that the last generation is a few years old.
            $start = now()->subYears((self::GENERATIONS - 1) * 3 + 3)->startOfYear();

            for ($generation = 0; $generation < self::GENERATIONS; $generation++) {
                $males = collect();
                $females = collect();
                $bornFrom = $start->copy()->addYears($generation * 3);

                for ($i = 0; $i < self::PER_GENERATION; $i++) {
                    $sex = $i % 2 === 0 ? Cat::MALE : Cat::FEMALE;
                    $born = $bornFrom->copy()->addDays($fake->numberBetween(0, 364));
                    $sireId = $generation === 0 ? null : $sires->random()->id;
                    $damId = $generation === 0 ? null : $dams->random()->id;
                    $regNumber = $this->regNumber($batch, ++$sequence);
                    $cattery = $fake->randomElement(self::CATTERIES);
                    $country = $fake->randomElement(array_keys(self::COUNTRIES));
                    $name = $fake->firstName();
                    $prefix = $fake->boolean(25) ? $fake->randomElement(['CH', 'GIC', 'EC']) : null;
                    $suffix = $fake->boolean(25) ? $fake->randomElement(['JW', 'DSM', 'WC']) : null;
                    $tests = $fake->boolean(70) ? $fake->randomElement(self::TEST_RESULTS) : null;
                    $owner = $fake->name();
                    $acquired = $born->copy()->addMonths($fake->numberBetween(6, 36));
                    if ($acquired->isFuture()) {
                        $acquired = now()->subDays($fake->numberBetween(1, 60));
                    }

                    $cat = Cat::create([
                        'name' => $name,
                        'sex' => $sex,
                        'sire_id' => $sireId,
                        'dam_id' => $damId,
                        'born_on' => $born->toDateString(),
                        'title_prefix' => $prefix,
                        'title_suffix' => $suffix,
                        'alt_name' => $fake->boolean(40) ? $fake->firstName() : null,
                        'print_name_1' => "{$cattery} {$name}",
                        'print_name_2' => $suffix ? "{$name} {$suffix}" : "{$name} of {$cattery}",
                        'breed_code' => $breeds->isEmpty() ? null : $breeds->random()->code,
                        'ems_code' => $ems->isEmpty() ? null : $ems->random()->code,
                        'chip_number' => $fake->numerify('985############'),
                        'genetic_tests' => $tests,
                        'genetic_tests_file' => $tests ? 'https://example.com/genetic-tests/'.$regNumber.'.pdf' : null,
                        'reg_number' => $regNumber,
                        'reg_number_last' => $fake->boolean(30) ? $this->regNumber('OLD-'.$batch, $sequence) : null,
                        'reg_number_2' => $fake->boolean(25) ? "{$batch}/{$fake->numberBetween(1, 999)}/{$born->year}" : null,
                        'reg_number_3' => $fake->boolean(10) ? "{$country}-{$fake->numberBetween(100, 9999)}/{$born->year}" : null,
                        'breeding_station' => $fake->boolean(50) ? "{$cattery} Cattery" : null,
                        'breeder' => $fake->name(),
                        'owner_name' => $owner,
                        'country_code' => $country,
                        'country_of_origin' => self::COUNTRIES[$country],
                        'country' => self::COUNTRIES[$fake->randomElement(array_keys(self::COUNTRIES))],
                        'notes' => 'Fake pedigree record generated for testing. '.$fake->sentence(8),
                        'ownership_notes' => $fake->boolean(60) ? "With {$owner} since {$acquired->year}." : null,
                        'personal_info' => $fake->boolean(60) ? $fake->randomElement(self::PERSONAL_NOTES) : null,
                        'photo' => $this->photo($fake->randomElement(self::PHOTOS)),
                        'vet_confirmation' => $fake->boolean(50) ? $fake->randomElement(self::VET_NOTES) : null,
                        'metadata' => ['doo' => $acquired->toDateString()],
                        'moderation_status' => 'approved',
                    ]);

                    $created['cats']++;
                    ($sex === Cat::MALE ? $males : $females)->push($cat);
                    $generations[$generation][] = $cat;

                    foreach ([$sireId, $damId] as $parentId) {
                        if ($parentId !== null && (! isset($lastChildBorn[$parentId]) || $lastChildBorn[$parentId]->lt($born))) {
                            $lastChildBorn[$parentId] = $born->copy();
                        }
                    }
                }

                $sires = $males;
                $dams = $females;
            }

            $this->burySome($generations, $lastChildBorn);

            return $created;
        });
    }

    /**
     * Give part of the older generations a death date and legacy metadata, but only
     * after their last recorded kitten was born.
     *
     * @param  array<int, list<Cat>>  $generations
     * @param  array<string, Carbon>  $lastChildBorn
     */
    private function burySome(array $generations, array $lastChildBorn): void
    {
        $fake = fake();

        foreach ([0 => 35, 1 => 20] as $generation => $chance) {
            foreach ($generations[$generation] ?? [] as $cat) {
                if (! $fake->boolean($chance)) {
                    continue;
                }

                $died = $cat->born_on->copy()->addYears($fake->numberBetween(7, 15));
                if (isset($lastChildBorn[$cat->id])) {
                    $died = $died->max($lastChildBorn[$cat->id]->copy()->addMonths(6));
                }
                if ($died->isFuture()) {
                    continue;
                }

                $cat->update([
                    'died_on' => $died->toDateString(),
                    'metadata' => array_merge($cat->metadata ?? [], [
                        'cemetery' => $fake->boolean(50) ? 'Garden of Peace, '.$fake->city() : null,
                    ]),
                ]);
            }
        }
    }

    private function regNumber(string $prefix, int $sequence): string
    {
        return $prefix.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function photo(string $name): string
    {
        return "https://placecats.com/{$name}/480/480?position=top";
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
