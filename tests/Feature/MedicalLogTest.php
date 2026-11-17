<?php

namespace Tests\Feature;

use App\Models\Litter;
use App\Models\MedicalLog;
use App\Models\Pig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_logs_attach_to_pigs_and_litters(): void
    {
        $pig = Pig::factory()->create();
        $litter = Litter::factory()->create();

        $pigLog = MedicalLog::factory()->create([
            'treatable_type' => Pig::class,
            'treatable_id' => $pig->id,
        ]);
        $litterLog = MedicalLog::factory()->create([
            'treatable_type' => Litter::class,
            'treatable_id' => $litter->id,
        ]);

        $this->assertTrue($pigLog->treatable->is($pig));
        $this->assertTrue($litterLog->treatable->is($litter));
        $this->assertTrue($pig->medicalLogs()->get()->contains($pigLog));
        $this->assertTrue($litter->medicalLogs()->get()->contains($litterLog));
    }

    public function test_due_within_scope_returns_only_upcoming_schedules(): void
    {
        $due = MedicalLog::factory()->create(['next_due_date' => today()->addDays(3)->toDateString()]);
        $overdue = MedicalLog::factory()->create(['next_due_date' => today()->subDays(3)->toDateString()]);
        $later = MedicalLog::factory()->create(['next_due_date' => today()->addDays(30)->toDateString()]);
        $completed = MedicalLog::factory()->create([
            'next_due_date' => today()->addDays(3)->toDateString(),
            'completed_at' => now(),
        ]);

        $ids = MedicalLog::query()->dueWithin(7)->pluck('id');

        $this->assertTrue($ids->contains($due->id));
        $this->assertFalse($ids->contains($overdue->id));
        $this->assertFalse($ids->contains($later->id));
        $this->assertFalse($ids->contains($completed->id));
    }

    public function test_overdue_scope_ignores_completed_schedules(): void
    {
        $overdue = MedicalLog::factory()->create(['next_due_date' => today()->subDays(3)->toDateString()]);
        $completed = MedicalLog::factory()->create([
            'next_due_date' => today()->subDays(3)->toDateString(),
            'completed_at' => now(),
        ]);

        $ids = MedicalLog::query()->overdue()->pluck('id');

        $this->assertTrue($ids->contains($overdue->id));
        $this->assertFalse($ids->contains($completed->id));
        $this->assertTrue($overdue->fresh()->isOverdue());
        $this->assertFalse($completed->fresh()->isOverdue());
    }

    public function test_administering_a_booster_completes_the_schedule_and_creates_the_next_one(): void
    {
        $original = MedicalLog::factory()->create([
            'next_due_date' => today()->toDateString(),
            'completed_at' => null,
        ]);

        $booster = $original->administerBooster(today()->addDays(21)->toDateString());

        $this->assertNotNull($original->fresh()->completed_at);
        $this->assertSame(today()->toDateString(), $booster->date_administered->toDateString());
        $this->assertSame(today()->addDays(21)->toDateString(), $booster->next_due_date->toDateString());
        $this->assertSame($original->treatable_id, $booster->treatable_id);
        $this->assertSame($original->medication_id, $booster->medication_id);
    }
}
