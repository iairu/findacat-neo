<?php

namespace App\Services;

use App\Models\Breed;
use App\Models\EmsColor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The standard breed list and EMS colour catalogue, loaded on demand and safe to run again.
 *
 * EMS colours are stored without a breed, which makes them available to every breed;
 * a colour row tied to one breed (as imported from the legacy data) still takes precedence for that breed.
 */
class ReferenceData
{
    /** @var array<string, string> */
    public const BREEDS = [
        'ABY' => 'Abyssinian', 'ACL' => 'American Curl Longhair', 'ACS' => 'American Curl Shorthair',
        'BAL' => 'Balinese', 'BEN' => 'Bengal', 'BLH' => 'British Longhair', 'BML' => 'Burmilla Longhair',
        'BMS' => 'Burmilla Shorthair', 'BSH' => 'British Shorthair', 'BUR' => 'Burmese', 'CHA' => 'Chartreux',
        'CRX' => 'Cornish Rex', 'CYM' => 'Cymric', 'DRX' => 'Devon Rex', 'DSX' => 'Don Sphynx',
        'EUR' => 'European Shorthair', 'EXO' => 'Exotic', 'JBT' => 'Japanese Bobtail', 'KBL' => 'Kurilian Bobtail Longhair',
        'KBS' => 'Kurilian Bobtail Shorthair', 'KOR' => 'Korat', 'LPL' => 'LaPerm Longhair', 'LPS' => 'LaPerm Shorthair',
        'MAN' => 'Manx', 'MAU' => 'Egyptian Mau', 'MCO' => 'Maine Coon', 'NEM' => 'Nebelung', 'NFO' => 'Norwegian Forest Cat',
        'OCI' => 'Ocicat', 'OLH' => 'Oriental Longhair', 'OSH' => 'Oriental Shorthair', 'PEB' => 'Peterbald',
        'PER' => 'Persian', 'RAG' => 'Ragdoll', 'RUS' => 'Russian Blue', 'SBI' => 'Sacred Birman', 'SIA' => 'Siamese',
        'SIB' => 'Siberian', 'SIN' => 'Singapura', 'SNO' => 'Snowshoe', 'SOK' => 'Sokoke', 'SOM' => 'Somali',
        'SPH' => 'Sphynx', 'SRL' => 'Selkirk Rex Longhair', 'SRS' => 'Selkirk Rex Shorthair', 'THA' => 'Thai',
        'TON' => 'Tonkinese', 'TUA' => 'Turkish Angora', 'TUV' => 'Turkish Van', 'YOR' => 'York Chocolate',
    ];

    /**
     * Coat colours by EMS letter: name, whether silver (s) and golden (y) variants exist.
     *
     * @var array<string, array{0: string, 1: bool, 2: bool}>
     */
    private const COLOURS = [
        'n' => ['Black', true, true], 'a' => ['Blue', true, true], 'b' => ['Chocolate', true, true],
        'c' => ['Lilac', true, true], 'o' => ['Cinnamon', true, true], 'p' => ['Fawn', true, true],
        'd' => ['Red', true, false], 'e' => ['Cream', true, false],
        'f' => ['Black tortie', true, false], 'g' => ['Blue tortie', true, false], 'h' => ['Chocolate tortie', true, false],
        'j' => ['Lilac tortie', true, false], 'q' => ['Cinnamon tortie', true, false], 'r' => ['Fawn tortie', true, false],
    ];

    /** @var array<string, string> Tabby pattern code => name (an empty code is a solid coat). */
    private const PATTERNS = ['' => '', '21' => 'blotched tabby', '22' => 'mackerel tabby', '23' => 'spotted tabby', '24' => 'ticked tabby'];

    /** @var array<string, string> White spotting code => name suffix. */
    private const WHITE_SPOTTING = ['' => '', '01' => ', van', '02' => ', harlequin', '03' => ', bicolour', '09' => ' and white'];

    /** @var array<string, string> */
    private const WHITE = ['w' => 'White', 'w 61' => 'White, blue eyes', 'w 62' => 'White, orange eyes', 'w 63' => 'White, odd eyes', 'w 64' => 'White, green eyes'];

    /**
     * Every catalogue colour as an EMS code and its English name.
     *
     * @return list<array{code: string, name: string}>
     */
    public static function colours(): array
    {
        $colours = [];
        foreach (self::WHITE as $code => $name) {
            $colours[] = ['code' => $code, 'name' => $name];
        }
        foreach (self::COLOURS as $letter => [$name, $silver, $golden]) {
            $variants = [[$letter, $name]];
            if ($silver) {
                $variants[] = [$letter.'s', $name.' silver'];
            }
            if ($golden) {
                $variants[] = [$letter.'y', $name.' golden'];
            }
            foreach ($variants as [$base, $baseName]) {
                foreach (self::WHITE_SPOTTING as $whiteCode => $whiteName) {
                    foreach (self::PATTERNS as $patternCode => $patternName) {
                        $colours[] = [
                            'code' => trim("$base $whiteCode $patternCode"),
                            'name' => trim("$baseName $patternName", ' ').$whiteName,
                        ];
                    }
                }
            }
        }

        return $colours;
    }

    /**
     * Add every missing breed and colour; existing rows are left untouched.
     *
     * @return array{breeds: int, ems: int} How many rows were added
     */
    public function load(): array
    {
        return DB::transaction(function (): array {
            $haveBreeds = Breed::query()->pluck('code')->map(fn (string $code) => EmsLookup::normalize($code))->flip();
            $breeds = collect(self::BREEDS)->reject(fn (string $name, string $code) => $haveBreeds->has(EmsLookup::normalize($code)));
            foreach ($breeds as $code => $name) {
                Breed::create(['code' => $code, 'name' => $name]);
            }

            $haveColours = EmsColor::query()->whereNull('breed_id')->pluck('code')->map(fn (string $code) => EmsLookup::normalize($code))->flip();
            $now = now();
            $rows = collect(self::colours())
                ->reject(fn (array $colour) => $haveColours->has(EmsLookup::normalize($colour['code'])))
                ->map(fn (array $colour) => [
                    'id' => (string) Str::uuid(), 'breed_id' => null, 'code' => $colour['code'],
                    'english_name' => $colour['name'], 'created_at' => $now, 'updated_at' => $now,
                ]);
            foreach ($rows->chunk(200) as $chunk) {
                EmsColor::query()->insert($chunk->all());
            }

            return ['breeds' => $breeds->count(), 'ems' => $rows->count()];
        });
    }
}
