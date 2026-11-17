<?php

namespace Database\Seeders;

use App\Models\Cage;
use App\Models\Pig;
use Illuminate\Database\Seeder;

class PigSeeder extends Seeder
{
    public function run(): void
    {
        $cages = Cage::query()->pluck('id', 'cage_number');

        $pigs = [
            // Sows
            ['tag_number' => 'SOW-001', 'type' => Pig::TYPE_SOW, 'breed' => 'Landrace', 'sex' => 'female', 'birth_date' => now()->subYears(3)->toDateString(), 'cage' => 'G1'],
            ['tag_number' => 'SOW-002', 'type' => Pig::TYPE_SOW, 'breed' => 'Large White', 'sex' => 'female', 'birth_date' => now()->subYears(2)->subMonths(6)->toDateString(), 'cage' => 'G2'],
            ['tag_number' => 'SOW-003', 'type' => Pig::TYPE_SOW, 'breed' => 'Landrace', 'sex' => 'female', 'birth_date' => now()->subYears(4)->toDateString(), 'cage' => 'G3'],
            ['tag_number' => 'SOW-004', 'type' => Pig::TYPE_SOW, 'breed' => 'Duroc', 'sex' => 'female', 'birth_date' => now()->subYears(2)->toDateString(), 'cage' => 'G4'],
            ['tag_number' => 'SOW-005', 'type' => Pig::TYPE_SOW, 'breed' => 'Large White', 'sex' => 'female', 'birth_date' => now()->subYears(3)->subMonths(2)->toDateString(), 'cage' => 'F1'],
            ['tag_number' => 'SOW-006', 'type' => Pig::TYPE_SOW, 'breed' => 'Landrace', 'sex' => 'female', 'birth_date' => now()->subYears(3)->subMonths(4)->toDateString(), 'cage' => 'F2'],
            ['tag_number' => 'SOW-007', 'type' => Pig::TYPE_SOW, 'breed' => 'Berkshire', 'sex' => 'female', 'birth_date' => now()->subYears(2)->subMonths(8)->toDateString(), 'cage' => 'F3'],
            ['tag_number' => 'SOW-008', 'type' => Pig::TYPE_SOW, 'breed' => 'Large White', 'sex' => 'female', 'birth_date' => now()->subYears(2)->subMonths(2)->toDateString(), 'cage' => 'Q1'],
            // Gilts
            ['tag_number' => 'GILT-001', 'type' => Pig::TYPE_GILT, 'breed' => 'Landrace', 'sex' => 'female', 'birth_date' => now()->subMonths(7)->toDateString(), 'cage' => 'W1'],
            ['tag_number' => 'GILT-002', 'type' => Pig::TYPE_GILT, 'breed' => 'Large White', 'sex' => 'female', 'birth_date' => now()->subMonths(6)->toDateString(), 'cage' => 'W1'],
            ['tag_number' => 'GILT-003', 'type' => Pig::TYPE_GILT, 'breed' => 'Duroc', 'sex' => 'female', 'birth_date' => now()->subMonths(8)->toDateString(), 'cage' => 'W1'],
            // Boars
            ['tag_number' => 'BOAR-001', 'type' => Pig::TYPE_BOAR, 'breed' => 'Duroc', 'sex' => 'male', 'birth_date' => now()->subYears(2)->subMonths(6)->toDateString(), 'cage' => 'B1'],
            ['tag_number' => 'BOAR-002', 'type' => Pig::TYPE_BOAR, 'breed' => 'Pietrain', 'sex' => 'male', 'birth_date' => now()->subYears(1)->subMonths(8)->toDateString(), 'cage' => 'B2'],
        ];

        foreach ($pigs as $pig) {
            Pig::firstOrCreate(
                ['tag_number' => $pig['tag_number']],
                [
                    'type' => $pig['type'],
                    'breed' => $pig['breed'],
                    'sex' => $pig['sex'],
                    'birth_date' => $pig['birth_date'],
                    'cage_id' => $cages[$pig['cage']] ?? null,
                    'status' => Pig::STATUS_ACTIVE,
                    'origin' => 'bred_on_farm',
                ],
            );
        }
    }
}
