<?php

namespace Tests\Feature;

use App\Filament\Resources\Breedings\Pages\CreateBreeding;
use App\Filament\Resources\Breedings\Pages\ListBreedings;
use App\Filament\Resources\FeedTransactions\Pages\CreateFeedTransaction;
use App\Filament\Resources\Litters\Pages\ListLitters;
use App\Filament\Resources\MedicalLogs\Pages\CreateMedicalLog;
use App\Models\Breeding;
use App\Models\Feed;
use App\Models\FeedTransaction;
use App\Models\Litter;
use App\Models\MedicalLog;
use App\Models\Medication;
use App\Models\Pig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_creating_a_breeding_from_the_panel_computes_the_due_date(): void
    {
        $sow = Pig::factory()->sow()->create();
        $boar = Pig::factory()->boar()->create();
        $breedingDate = today()->subDays(10);

        Livewire::test(CreateBreeding::class)
            ->fillForm([
                'sow_id' => $sow->id,
                'boar_id' => $boar->id,
                'breeding_date' => $breedingDate->toDateString(),
                'method' => 'natural',
                'pregnancy_status' => Breeding::STATUS_PENDING,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $breeding = Breeding::query()->firstOrFail();

        $this->assertSame(
            $breedingDate->copy()->addDays(Breeding::GESTATION_DAYS)->toDateString(),
            $breeding->expected_farrowing_date->toDateString(),
        );
    }

    public function test_the_panel_blocks_a_second_active_breeding_for_the_same_sow(): void
    {
        $sow = Pig::factory()->sow()->create();
        Breeding::factory()->confirmed()->create(['sow_id' => $sow->id]);

        Livewire::test(CreateBreeding::class)
            ->fillForm([
                'sow_id' => $sow->id,
                'breeding_date' => today()->toDateString(),
                'method' => 'natural',
                'pregnancy_status' => Breeding::STATUS_PENDING,
            ])
            ->call('create')
            ->assertHasFormErrors(['sow_id']);
    }

    public function test_recording_a_farrowing_from_the_breeding_table_creates_a_litter(): void
    {
        $breeding = Breeding::factory()->confirmed()->create([
            'breeding_date' => today()->subDays(Breeding::GESTATION_DAYS)->toDateString(),
        ]);

        Livewire::test(ListBreedings::class)
            ->callTableAction('recordFarrowing', $breeding, data: [
                'farrowing_date' => today()->toDateString(),
                'born_alive' => 8,
                'stillborn' => 1,
                'mummified' => 0,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('farrowings', [
            'breeding_id' => $breeding->id,
            'born_alive' => 8,
            'stillborn' => 1,
        ]);

        $this->assertDatabaseHas('litters', ['current_count' => 8]);
        $this->assertSame(Breeding::STATUS_FARROWED, $breeding->fresh()->pregnancy_status);
    }

    public function test_stock_in_transaction_from_the_panel_updates_feed_stock(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 50]);

        Livewire::test(CreateFeedTransaction::class)
            ->fillForm([
                'feed_id' => $feed->id,
                'type' => FeedTransaction::TYPE_IN,
                'quantity_kg' => 250,
                'transaction_date' => today()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('250.00', $feed->fresh()->stock_kg);
        $this->assertSame(5.0, $feed->fresh()->stock_in_bags);
        $this->assertSame(auth()->id(), FeedTransaction::query()->firstOrFail()->user_id);
    }

    public function test_the_panel_blocks_a_stock_out_larger_than_the_available_stock(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 40]);

        Livewire::test(CreateFeedTransaction::class)
            ->fillForm([
                'feed_id' => $feed->id,
                'type' => FeedTransaction::TYPE_OUT,
                'quantity_kg' => 100,
                'transaction_date' => today()->toDateString(),
            ])
            ->call('create')
            ->assertHasFormErrors(['quantity_kg']);

        $this->assertSame(0, FeedTransaction::query()->count());
    }

    public function test_creating_a_medical_log_for_a_pig_from_the_panel(): void
    {
        $pig = Pig::factory()->sow()->create();
        $medication = Medication::factory()->create();

        Livewire::test(CreateMedicalLog::class)
            ->fillForm([
                'treatable_type' => Pig::class,
                'treatable_id' => $pig->id,
                'type' => MedicalLog::TYPE_VACCINE,
                'medication_id' => $medication->id,
                'date_administered' => today()->toDateString(),
                'next_due_date' => today()->addDays(21)->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $log = MedicalLog::query()->firstOrFail();

        $this->assertSame(Pig::class, $log->treatable_type);
        $this->assertTrue($log->treatable->is($pig));
        $this->assertSame(today()->addDays(21)->toDateString(), $log->next_due_date->toDateString());
    }

    public function test_weaning_a_litter_from_the_panel(): void
    {
        $litter = Litter::factory()->create([
            'current_count' => 9,
            'status' => Litter::STATUS_NURSING,
        ]);

        Livewire::test(ListLitters::class)
            ->callTableAction('wean', $litter, data: ['weaned_count' => 9])
            ->assertHasNoTableActionErrors();

        $litter->refresh();

        $this->assertSame(Litter::STATUS_WEANED, $litter->status);
        $this->assertSame(9, $litter->weaned_count);
    }
}
