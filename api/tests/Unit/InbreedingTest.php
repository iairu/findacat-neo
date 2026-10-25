<?php

namespace Tests\Unit;

use App\Services\Inbreeding;
use PHPUnit\Framework\TestCase;

class InbreedingTest extends TestCase
{
    /** Two unrelated founders F1/F2 produce full sibs S1/S2 and half sibs H (by F1) via an unrelated mate. */
    private function family(): Inbreeding
    {
        return new Inbreeding([
            'f1' => [null, null], 'f2' => [null, null], 'm' => [null, null],
            's1' => ['f1', 'f2'], 's2' => ['f1', 'f2'],
            'h' => ['f1', 'm'],
        ]);
    }

    public function test_unrelated_parents_give_zero(): void
    {
        $this->assertSame(0.0, $this->family()->ofMating('f1', 'f2'));
        $this->assertSame(0.0, $this->family()->ofMating(null, 's1'));
    }

    public function test_full_siblings_give_a_quarter(): void
    {
        $this->assertEqualsWithDelta(0.25, $this->family()->ofMating('s1', 's2'), 1e-12);
    }

    public function test_half_siblings_give_an_eighth(): void
    {
        $this->assertEqualsWithDelta(0.125, $this->family()->ofMating('s1', 'h'), 1e-12);
    }

    public function test_parent_with_offspring_gives_a_quarter(): void
    {
        $this->assertEqualsWithDelta(0.25, $this->family()->ofMating('f1', 's1'), 1e-12);
    }

    public function test_grandparent_with_grandchild_gives_an_eighth(): void
    {
        $calc = new Inbreeding([
            'g' => [null, null], 'p' => ['g', null], 'c' => ['p', null],
        ]);
        $this->assertEqualsWithDelta(0.125, $calc->ofMating('g', 'c'), 1e-12);
    }

    public function test_first_cousins_give_one_sixteenth(): void
    {
        $calc = new Inbreeding([
            'g1' => [null, null], 'g2' => [null, null],
            'a' => ['g1', 'g2'], 'b' => ['g1', 'g2'],
            'x' => ['a', null], 'y' => ['b', null],
        ]);
        $this->assertEqualsWithDelta(0.0625, $calc->ofMating('x', 'y'), 1e-12);
    }

    public function test_inbred_common_ancestor_raises_the_result(): void
    {
        // A is the child of full sibs (F = 0.25); S and D share only A: 0.5^3 * 1.25.
        $calc = new Inbreeding([
            'f1' => [null, null], 'f2' => [null, null],
            'p' => ['f1', 'f2'], 'q' => ['f1', 'f2'],
            'a' => ['p', 'q'],
            's' => ['a', null], 'd' => ['a', null],
        ]);
        $this->assertEqualsWithDelta(0.25, $calc->coefficient('a'), 1e-12);
        $this->assertEqualsWithDelta(0.15625, $calc->ofMating('s', 'd'), 1e-12);
    }

    public function test_coefficient_of_a_stored_cat(): void
    {
        $this->assertEqualsWithDelta(0.0, $this->family()->coefficient('s1'), 1e-12);
        $calc = new Inbreeding(['a' => [null, null], 'b' => ['a', null], 'c' => ['a', 'b']]);
        $this->assertEqualsWithDelta(0.25, $calc->coefficient('c'), 1e-12);
    }

    public function test_cycles_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Inbreeding(['x' => ['y', null], 'y' => ['x', null]]))->ofMating('x', 'y');
    }
}
