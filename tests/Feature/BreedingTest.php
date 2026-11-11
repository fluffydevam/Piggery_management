<?php

namespace Tests\Feature;

use App\Models\Breeding;
use App\Models\Pig;
use App\Rules\OneActiveBreedingPerSow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_breeding_computes_expected_farrowing_date(): void
    {
        $sow = Pig::factory()->sow()->create();
        $boar = Pig::factory()->boar()->create();

        $breeding = Breeding::create([
            'sow_id' => $sow->id,
            'boar_id' => $boar->id,
            'breeding_date' => today()->subDays(10)->toDateString(),
            'method' => 'natural',
        ]);

        $this->assertSame(
            today()->subDays(10)->addDays(Breeding::GESTATION_DAYS)->toDateString(),
            $breeding->expected_farrowing_date->toDateString(),
        );
    }

    public function test_upcoming_farrowing_scope_returns_only_pregnancies_due_in_window(): void
    {
        $dueSoon = Breeding::factory()->confirmed()->create([
            'breeding_date' => today()->subDays(Breeding::GESTATION_DAYS - 4)->toDateString(),
        ]);

        $dueLater = Breeding::factory()->confirmed()->create([
            'breeding_date' => today()->subDays(60)->toDateString(),
        ]);

        $notPregnant = Breeding::factory()->create([
            'breeding_date' => today()->subDays(Breeding::GESTATION_DAYS - 4)->toDateString(),
            'pregnancy_status' => Breeding::STATUS_NOT_PREGNANT,
        ]);

        $ids = Breeding::query()->upcomingFarrowing(7)->pluck('id');

        $this->assertTrue($ids->contains($dueSoon->id));
        $this->assertFalse($ids->contains($dueLater->id));
        $this->assertFalse($ids->contains($notPregnant->id));
    }

    public function test_overdue_farrowing_scope_returns_past_due_pregnancies_only(): void
    {
        $overdue = Breeding::factory()->confirmed()->create([
            'breeding_date' => today()->subDays(Breeding::GESTATION_DAYS + 6)->toDateString(),
        ]);

        $onTrack = Breeding::factory()->confirmed()->create([
            'breeding_date' => today()->subDays(100)->toDateString(),
        ]);

        $ids = Breeding::query()->overdueFarrowing()->pluck('id');

        $this->assertTrue($ids->contains($overdue->id));
        $this->assertFalse($ids->contains($onTrack->id));
    }

    public function test_record_farrowing_creates_farrowing_litter_and_updates_status(): void
    {
        $breeding = Breeding::factory()->confirmed()->create([
            'breeding_date' => today()->subDays(Breeding::GESTATION_DAYS)->toDateString(),
        ]);

        $farrowing = $breeding->recordFarrowing([
            'born_alive' => 9,
            'stillborn' => 1,
            'mummified' => 0,
        ]);

        $this->assertDatabaseHas('farrowings', [
            'breeding_id' => $breeding->id,
            'born_alive' => 9,
            'stillborn' => 1,
        ]);

        $this->assertDatabaseHas('litters', [
            'farrowing_id' => $farrowing->id,
            'current_count' => 9,
            'status' => 'nursing',
        ]);

        $this->assertSame(Breeding::STATUS_FARROWED, $breeding->fresh()->pregnancy_status);
        $this->assertSame(10, $farrowing->fresh()->total_born);
    }

    public function test_one_active_breeding_rule_blocks_a_second_active_pregnancy(): void
    {
        $sow = Pig::factory()->sow()->create();
        $existing = Breeding::factory()->confirmed()->create(['sow_id' => $sow->id]);

        $failure = null;
        (new OneActiveBreedingPerSow())->validate('sow_id', $sow->id, function (string $message) use (&$failure) {
            $failure = $message;
        });
        $this->assertNotNull($failure);

        // Editing the same record must not conflict with itself.
        $failure = null;
        (new OneActiveBreedingPerSow($existing->id))->validate('sow_id', $sow->id, function (string $message) use (&$failure) {
            $failure = $message;
        });
        $this->assertNull($failure);
    }
}
