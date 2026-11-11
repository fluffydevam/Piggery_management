<?php

namespace Database\Seeders;

use App\Models\Cage;
use Illuminate\Database\Seeder;

class CageSeeder extends Seeder
{
    public function run(): void
    {
        $cages = [
            ['cage_number' => 'G1', 'type' => Cage::TYPE_GESTATION, 'capacity' => 1, 'location' => 'Barn A'],
            ['cage_number' => 'G2', 'type' => Cage::TYPE_GESTATION, 'capacity' => 1, 'location' => 'Barn A'],
            ['cage_number' => 'G3', 'type' => Cage::TYPE_GESTATION, 'capacity' => 1, 'location' => 'Barn A'],
            ['cage_number' => 'G4', 'type' => Cage::TYPE_GESTATION, 'capacity' => 1, 'location' => 'Barn A'],
            ['cage_number' => 'F1', 'type' => Cage::TYPE_FARROWING, 'capacity' => 1, 'location' => 'Barn B'],
            ['cage_number' => 'F2', 'type' => Cage::TYPE_FARROWING, 'capacity' => 1, 'location' => 'Barn B'],
            ['cage_number' => 'F3', 'type' => Cage::TYPE_FARROWING, 'capacity' => 1, 'location' => 'Barn B'],
            ['cage_number' => 'B1', 'type' => Cage::TYPE_BOAR, 'capacity' => 1, 'location' => 'Barn C'],
            ['cage_number' => 'B2', 'type' => Cage::TYPE_BOAR, 'capacity' => 1, 'location' => 'Barn C'],
            ['cage_number' => 'W1', 'type' => Cage::TYPE_WEANING, 'capacity' => 12, 'location' => 'Open shed'],
            ['cage_number' => 'W2', 'type' => Cage::TYPE_WEANING, 'capacity' => 12, 'location' => 'Open shed'],
            ['cage_number' => 'Q1', 'type' => Cage::TYPE_QUARANTINE, 'capacity' => 2, 'location' => 'Open shed'],
        ];

        foreach ($cages as $cage) {
            Cage::firstOrCreate(
                ['cage_number' => $cage['cage_number']],
                $cage + ['status' => Cage::STATUS_AVAILABLE],
            );
        }
    }
}
