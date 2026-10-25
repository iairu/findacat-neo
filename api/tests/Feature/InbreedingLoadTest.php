<?php

namespace Tests\Feature;

use App\Models\Cat;
use App\Services\Inbreeding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InbreedingLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_pedigrees_are_loaded_from_the_database(): void
    {
        $a = Cat::create(['name' => 'A', 'sex' => Cat::MALE]);
        $b = Cat::create(['name' => 'B', 'sex' => Cat::FEMALE]);
        $s1 = Cat::create(['name' => 'S1', 'sex' => Cat::MALE, 'sire_id' => $a->id, 'dam_id' => $b->id]);
        $s2 = Cat::create(['name' => 'S2', 'sex' => Cat::FEMALE, 'sire_id' => $a->id, 'dam_id' => $b->id]);
        $kitten = Cat::create(['name' => 'K', 'sex' => Cat::FEMALE, 'sire_id' => $s1->id, 'dam_id' => $s2->id]);

        $calc = Inbreeding::forCats([$kitten->id]);

        $this->assertEqualsWithDelta(0.25, $calc->coefficient($kitten->id), 1e-12);
        $this->assertEqualsWithDelta(0.25, Inbreeding::forCats([$s1->id, $s2->id])->ofMating($s1->id, $s2->id), 1e-12);
    }
}
