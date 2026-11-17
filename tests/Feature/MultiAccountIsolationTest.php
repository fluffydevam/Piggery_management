<?php

namespace Tests\Feature;

use App\Filament\Resources\Breedings\Pages\CreateBreeding;
use App\Filament\Resources\Medications\MedicationResource;
use App\Filament\Resources\Pigs\PigResource;
use App\Models\Cage;
use App\Models\Medication;
use App\Models\Pig;
use App\Models\User;
use Filament\Auth\Pages\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiAccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_account_only_sees_its_own_records(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $alicePig = Pig::factory()->for($alice)->create();
        $bobPig = Pig::factory()->for($bob)->create();

        $this->actingAs($alice);

        $this->assertSame([$alicePig->id], Pig::query()->pluck('id')->all());
        $this->assertNull(Pig::query()->find($bobPig->id));
    }

    public function test_two_accounts_can_reuse_the_same_tag_and_cage_number(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        Pig::factory()->for($alice)->create(['tag_number' => 'PIG-001']);
        Pig::factory()->for($bob)->create(['tag_number' => 'PIG-001']);

        Cage::factory()->for($alice)->create(['cage_number' => 'G1']);
        Cage::factory()->for($bob)->create(['cage_number' => 'G1']);

        $this->assertSame(2, Pig::withoutUserScope()->where('tag_number', 'PIG-001')->count());
        $this->assertSame(2, Cage::withoutUserScope()->where('cage_number', 'G1')->count());
    }

    public function test_a_new_record_inherits_the_authenticated_account(): void
    {
        $alice = User::factory()->create();

        $this->actingAs($alice);

        $pig = Pig::create([
            'tag_number' => 'X-1',
            'type' => Pig::TYPE_SOW,
            'sex' => 'female',
        ]);

        $this->assertSame($alice->id, $pig->user_id);
    }

    public function test_a_user_cannot_open_another_accounts_pig_in_the_panel(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $bobPig = Pig::factory()->for($bob)->create();

        $this->actingAs($alice)
            ->get(PigResource::getUrl('edit', ['record' => $bobPig]))
            ->assertNotFound();
    }

    public function test_the_breeding_form_rejects_another_accounts_sow(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $bobSow = Pig::factory()->for($bob)->sow()->create();

        $this->actingAs($alice);

        Livewire::test(CreateBreeding::class)
            ->fillForm([
                'sow_id' => $bobSow->id,
                'breeding_date' => today()->subDays(10)->toDateString(),
                'method' => 'natural',
                'pregnancy_status' => 'pending',
            ])
            ->call('create')
            ->assertHasFormErrors(['sow_id']);
    }

    public function test_the_shared_medication_catalog_is_read_only(): void
    {
        $user = User::factory()->create();
        $shared = Medication::factory()->create(['user_id' => null]);
        $own = Medication::factory()->for($user)->create();

        $this->actingAs($user);

        $this->assertTrue(Medication::query()->whereKey($shared->id)->exists());
        $this->assertTrue(Medication::query()->whereKey($own->id)->exists());

        $this->get(MedicationResource::getUrl('edit', ['record' => $shared]))
            ->assertForbidden();

        $this->get(MedicationResource::getUrl('edit', ['record' => $own]))
            ->assertOk();
    }

    public function test_registration_creates_an_account_with_a_starter_farm(): void
    {
        $this->get('/admin/register')->assertOk();

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'New Farmer',
                'email' => 'new-farmer@example.com',
                'password' => 'Sup3r-Secret-2026',
                'passwordConfirmation' => 'Sup3r-Secret-2026',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'new-farmer@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(count(config('farm.starter_cages')), $user->cages()->count());
        $this->assertSame(0, $user->pigs()->count());
    }
}
