<?php

namespace Tests\Unit;

use App\Models\Breeding;
use Tests\TestCase;

class BreedingMathTest extends TestCase
{
    public function test_gestation_period_constant_is_114_days(): void
    {
        $this->assertSame(114, Breeding::GESTATION_DAYS);
    }

    public function test_expected_farrowing_date_is_breeding_date_plus_114_days(): void
    {
        $breeding = new Breeding(['breeding_date' => '2026-01-01']);
        $breeding->computeExpectedFarrowingDate();

        $this->assertSame('2026-04-25', $breeding->expected_farrowing_date->toDateString());
    }

    public function test_gestation_calculation_crosses_the_year_boundary(): void
    {
        $breeding = new Breeding(['breeding_date' => '2026-12-20']);
        $breeding->computeExpectedFarrowingDate();

        $this->assertSame('2027-04-13', $breeding->expected_farrowing_date->toDateString());
    }
}
