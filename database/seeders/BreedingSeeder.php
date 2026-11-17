<?php

namespace Database\Seeders;

use App\Models\Breeding;
use App\Models\Pig;
use Illuminate\Database\Seeder;

class BreedingSeeder extends Seeder
{
    public function run(): void
    {
        if (Breeding::query()->exists()) {
            return;
        }

        $pigs = Pig::query()->pluck('id', 'tag_number');

        $boarA = $pigs['BOAR-001'] ?? null;
        $boarB = $pigs['BOAR-002'] ?? null;

        // --- Farrowed litter still nursing (ready to wean: 25 days old) ---
        $nursing = Breeding::create([
            'sow_id' => $pigs['SOW-005'],
            'boar_id' => $boarA,
            'breeding_date' => today()->subDays(139)->toDateString(),
            'method' => 'natural',
            'pregnancy_status' => Breeding::STATUS_CONFIRMED,
            'confirmed_pregnant_at' => today()->subDays(120)->toDateString(),
        ]);

        $nursing->recordFarrowing([
            'farrowing_date' => today()->subDays(25)->toDateString(),
            'born_alive' => 10,
            'stillborn' => 1,
            'mummified' => 0,
            'notes' => 'Unassisted farrowing, strong piglets.',
        ]);

        // --- Farrowed litter already weaned ---
        $weaned = Breeding::create([
            'sow_id' => $pigs['SOW-006'],
            'boar_id' => $boarB,
            'breeding_date' => today()->subDays(159)->toDateString(),
            'method' => 'ai',
            'semen_source' => 'Duroc AI batch #A-09',
            'pregnancy_status' => Breeding::STATUS_CONFIRMED,
            'confirmed_pregnant_at' => today()->subDays(140)->toDateString(),
        ]);

        $weaned->recordFarrowing([
            'farrowing_date' => today()->subDays(45)->toDateString(),
            'born_alive' => 12,
            'stillborn' => 0,
            'mummified' => 1,
            'notes' => 'Large litter, one piglet lost in week 2.',
        ]);

        $weanedLitter = $weaned->litter;
        $weanedLitter->recordDeath(1);
        $weanedLitter->wean(today()->subDays(24)->toDateString());

        // --- Pregnant sows, farrowings coming up ---
        Breeding::create([
            'sow_id' => $pigs['SOW-001'],
            'boar_id' => $boarA,
            'breeding_date' => today()->subDays(107)->toDateString(),
            'method' => 'natural',
            'pregnancy_status' => Breeding::STATUS_CONFIRMED,
            'confirmed_pregnant_at' => today()->subDays(90)->toDateString(),
        ]); // expected farrowing: today + 7 days

        Breeding::create([
            'sow_id' => $pigs['SOW-002'],
            'boar_id' => $boarB,
            'breeding_date' => today()->subDays(110)->toDateString(),
            'method' => 'ai',
            'semen_source' => 'Duroc AI batch #A-12',
            'pregnancy_status' => Breeding::STATUS_PENDING,
        ]); // expected farrowing: today + 4 days

        // --- Overdue: expected date already passed ---
        Breeding::create([
            'sow_id' => $pigs['SOW-003'],
            'boar_id' => $boarA,
            'breeding_date' => today()->subDays(120)->toDateString(),
            'method' => 'natural',
            'pregnancy_status' => Breeding::STATUS_CONFIRMED,
            'confirmed_pregnant_at' => today()->subDays(100)->toDateString(),
        ]); // expected farrowing: today - 6 days (overdue)

        // --- Early pregnancy ---
        Breeding::create([
            'sow_id' => $pigs['SOW-004'],
            'boar_id' => $boarB,
            'breeding_date' => today()->subDays(30)->toDateString(),
            'method' => 'natural',
            'pregnancy_status' => Breeding::STATUS_PENDING,
        ]); // expected farrowing: today + 84 days

        // --- Not pregnant ---
        Breeding::create([
            'sow_id' => $pigs['SOW-007'],
            'boar_id' => $boarA,
            'breeding_date' => today()->subDays(40)->toDateString(),
            'method' => 'natural',
            'pregnancy_status' => Breeding::STATUS_NOT_PREGNANT,
        ]);

        // --- Mid pregnancy ---
        Breeding::create([
            'sow_id' => $pigs['SOW-008'],
            'boar_id' => $boarB,
            'breeding_date' => today()->subDays(60)->toDateString(),
            'method' => 'ai',
            'pregnancy_status' => Breeding::STATUS_CONFIRMED,
            'confirmed_pregnant_at' => today()->subDays(45)->toDateString(),
        ]); // expected farrowing: today + 54 days
    }
}
