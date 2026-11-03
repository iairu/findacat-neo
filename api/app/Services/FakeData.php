<?php

namespace App\Services;

/**
 * A tiny stand-in for the parts of Faker the demo data needs.
 *
 * Faker is a dev-only dependency and is absent from production installs
 * (`composer install --no-dev`), so the seeder must not rely on it.
 */
class FakeData
{
    private const FIRST_NAMES = [
        'Luna', 'Milo', 'Bella', 'Simba', 'Nala', 'Oscar', 'Chloe', 'Leo', 'Mia', 'Felix', 'Cleo', 'Tiger', 'Zoe', 'Max',
        'Lily', 'Ziggy', 'Ruby', 'Jasper', 'Sophie', 'Oliver', 'Maya', 'Toby', 'Stella', 'Binky', 'Willow', 'Smokey',
    ];

    private const SURNAMES = [
        'Novak', 'Horvath', 'Kovac', 'Varga', 'Toth', 'Kral', 'Molnar', 'Balaz', 'Urban', 'Kubik', 'Sedlak', 'Fiala',
        'Weber', 'Bauer', 'Nowak', 'Kaminski',
    ];

    private const CITIES = ['Bratislava', 'Kosice', 'Zilina', 'Brno', 'Prague', 'Vienna', 'Graz', 'Budapest', 'Krakow', 'Munich'];

    private const WORDS = [
        'calm', 'playful', 'curious', 'gentle', 'bright', 'quiet', 'friendly', 'sturdy', 'graceful', 'loyal', 'sleepy',
        'clever', 'cat', 'kitten', 'litter', 'coat', 'whiskers', 'pedigree', 'cattery', 'show', 'home', 'garden',
    ];

    public function firstName(): string
    {
        return $this->randomElement(self::FIRST_NAMES);
    }

    public function name(): string
    {
        return $this->firstName().' '.$this->randomElement(self::SURNAMES);
    }

    public function city(): string
    {
        return $this->randomElement(self::CITIES);
    }

    public function sentence(int $words = 6): string
    {
        $picked = [];
        for ($i = 0; $i < $words; $i++) {
            $picked[] = $this->randomElement(self::WORDS);
        }

        return ucfirst(implode(' ', $picked)).'.';
    }

    /** True with the given percentage chance. */
    public function boolean(int $chance = 50): bool
    {
        return random_int(1, 100) <= $chance;
    }

    public function numberBetween(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * @template T
     *
     * @param  array<int|string, T>  $items
     * @return T
     */
    public function randomElement(array $items): mixed
    {
        $values = array_values($items);

        return $values[random_int(0, count($values) - 1)];
    }

    /** Replace every # with a random digit. */
    public function numerify(string $pattern): string
    {
        return (string) preg_replace_callback('/#/', fn () => (string) random_int(0, 9), $pattern);
    }
}
