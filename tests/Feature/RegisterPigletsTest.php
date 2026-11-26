<?php

namespace Tests\Feature;

use App\Filament\Resources\Litters\LitterResource;
use App\Filament\Resources\Litters\Pages\ListLitters;
use App\Filament\Resources\Litters\Pages\RegisterPiglets;
use App\Models\Breeding;
use App\Models\Cage;
use App\Models\Farrowing;
use App\Models\Litter;
use App\Models\Pig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterPigletsTest extends TestCase
{
    use RefreshDatabase;

    private function litterFor(User $user, int $aliveCount = 3): Litter
    {
        $sow = Pig::factory()->for($user)->sow()->create();
        $boar = Pig::factory()->for($user)->boar()->create();

        $breeding = Breeding::factory()->for($user)->farrowed()->create([
            'sow_id' => $sow->id,
            'boar_id' => $boar->id,
        ]);

        $farrowing = Farrowing::factory()->for($user)->create([
            'breeding_id' => $breeding->id,
            'farrowing_date' => today()->subDays(10)->toDateString(),
        ]);

        return Litter::factory()->for($user)->create([
            'farrowing_id' => $farrowing->id,
            'current_count' => $aliveCount,
        ]);
    }

    public function test_registering_piglets_creates_individual_pig_records(): void
    {
        $user = User::factory()->create();
        $litter = $this->litterFor($user, 2);
        $cage = Cage::factory()->for($user)->create();

        $this->actingAs($user);

        Livewire::test(RegisterPiglets::class, ['record' => $litter->getRouteKey()])
            ->fillForm([
                'piglets' => [
                    ['tag_number' => 'PIGLET-1', 'sex' => 'female', 'cage_id' => $cage->id, 'notes' => 'First'],
                    ['tag_number' => 'PIGLET-2', 'sex' => 'male', 'cage_id' => null, 'notes' => null],
                ],
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $litter->piglets()->count());

        $this->assertDatabaseHas('pigs', [
            'user_id' => $user->id,
            'litter_id' => $litter->id,
            'tag_number' => 'PIGLET-1',
            'type' => Pig::TYPE_PIGLET,
            'sex' => 'female',
            'cage_id' => $cage->id,
            'origin' => 'bred_on_farm',
        ]);

        $this->assertDatabaseHas('pigs', [
            'user_id' => $user->id,
            'litter_id' => $litter->id,
            'tag_number' => 'PIGLET-2',
            'type' => Pig::TYPE_PIGLET,
            'sex' => 'male',
        ]);
    }

    public function test_a_tag_already_used_by_the_same_account_is_rejected(): void
    {
        $user = User::factory()->create();
        $litter = $this->litterFor($user, 1);
        Pig::factory()->for($user)->create(['tag_number' => 'TAKEN-1']);

        $this->actingAs($user);

        Livewire::test(RegisterPiglets::class, ['record' => $litter->getRouteKey()])
            ->fillForm([
                'piglets' => [
                    ['tag_number' => 'TAKEN-1', 'sex' => 'female'],
                ],
            ])
            ->call('register')
            ->assertHasFormErrors(['piglets.0.tag_number']);

        $this->assertSame(0, $litter->piglets()->count());
    }

    public function test_a_tag_used_by_another_account_is_not_a_conflict(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $litter = $this->litterFor($alice, 1);
        Pig::factory()->for($bob)->create(['tag_number' => 'BOB-ONLY']);

        $this->actingAs($alice);

        Livewire::test(RegisterPiglets::class, ['record' => $litter->getRouteKey()])
            ->fillForm([
                'piglets' => [
                    ['tag_number' => 'BOB-ONLY', 'sex' => 'female'],
                ],
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pigs', [
            'user_id' => $alice->id,
            'tag_number' => 'BOB-ONLY',
            'type' => Pig::TYPE_PIGLET,
        ]);
    }

    public function test_another_accounts_cage_is_rejected(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $litter = $this->litterFor($alice, 1);
        $bobCage = Cage::factory()->for($bob)->create();

        $this->actingAs($alice);

        Livewire::test(RegisterPiglets::class, ['record' => $litter->getRouteKey()])
            ->fillForm([
                'piglets' => [
                    ['tag_number' => 'PIGLET-X', 'sex' => 'female', 'cage_id' => $bobCage->id],
                ],
            ])
            ->call('register')
            ->assertHasFormErrors(['piglets.0.cage_id']);

        $this->assertSame(0, $litter->piglets()->count());
    }

    public function test_another_accounts_litter_cannot_be_opened(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $bobLitter = $this->litterFor($bob, 2);

        $this->actingAs($alice)
            ->get(LitterResource::getUrl('register-piglets', ['record' => $bobLitter]))
            ->assertNotFound();
    }

    public function test_the_litter_action_links_to_the_registration_page(): void
    {
        $user = User::factory()->create();
        $litter = $this->litterFor($user, 2);

        $this->actingAs($user);

        Livewire::test(ListLitters::class)
            ->assertTableActionHasUrl(
                'keepAsPiglets',
                LitterResource::getUrl('register-piglets', ['record' => $litter]),
                $litter,
            );
    }
}
