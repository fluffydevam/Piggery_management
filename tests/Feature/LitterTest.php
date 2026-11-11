<?php

namespace Tests\Feature;

use App\Models\Farrowing;
use App\Models\Litter;
use App\Models\Pig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LitterTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_death_updates_counts_and_marks_litter_lost_at_zero(): void
    {
        $litter = Litter::factory()->create([
            'current_count' => 2,
            'deaths_count' => 0,
        ]);

        $litter->recordDeath();
        $litter->refresh();

        $this->assertSame(1, $litter->current_count);
        $this->assertSame(1, $litter->deaths_count);
        $this->assertSame(Litter::STATUS_NURSING, $litter->status);

        $litter->recordDeath();
        $litter->refresh();

        $this->assertSame(0, $litter->current_count);
        $this->assertSame(Litter::STATUS_LOST, $litter->status);
    }

    public function test_ready_to_wean_scope_uses_the_21_day_cutoff(): void
    {
        $oldFarrowing = Farrowing::factory()->create([
            'farrowing_date' => today()->subDays(25)->toDateString(),
        ]);
        $youngFarrowing = Farrowing::factory()->create([
            'farrowing_date' => today()->subDays(10)->toDateString(),
        ]);

        $old = Litter::factory()->create(['farrowing_id' => $oldFarrowing->id]);
        $young = Litter::factory()->create(['farrowing_id' => $youngFarrowing->id]);

        $ids = Litter::query()->readyToWean()->pluck('id');

        $this->assertTrue($ids->contains($old->id));
        $this->assertFalse($ids->contains($young->id));
    }

    public function test_weaning_sets_the_weaned_fields(): void
    {
        $litter = Litter::factory()->create(['current_count' => 9]);

        $litter->wean(today()->toDateString());
        $litter->refresh();

        $this->assertSame(Litter::STATUS_WEANED, $litter->status);
        $this->assertSame(9, $litter->weaned_count);
        $this->assertTrue($litter->weaned_date->isToday());
    }

    public function test_promote_to_gilt_creates_a_herd_pig(): void
    {
        $farrowing = Farrowing::factory()->create([
            'farrowing_date' => today()->subDays(30)->toDateString(),
        ]);
        $litter = Litter::factory()->create(['farrowing_id' => $farrowing->id]);

        $gilt = $litter->promoteToGilt('GILT-TEST-01');

        $this->assertInstanceOf(Pig::class, $gilt);
        $this->assertSame(Pig::TYPE_GILT, $gilt->type);
        $this->assertSame('female', $gilt->sex);
        $this->assertSame($farrowing->farrowing_date->toDateString(), $gilt->birth_date->toDateString());
        $this->assertDatabaseHas('pigs', ['tag_number' => 'GILT-TEST-01']);
    }
}
