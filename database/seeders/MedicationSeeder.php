<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        $medications = [
            ['name' => 'Iron Dextran', 'type' => 'vitamin', 'default_dosage' => '2 mL', 'withdrawal_days' => 0, 'notes' => 'Given to piglets at 3 days old to prevent anaemia.'],
            ['name' => 'Parvovirus Vaccine', 'type' => 'vaccine', 'default_dosage' => '2 mL', 'withdrawal_days' => 0, 'notes' => 'Breeding sows, annual booster.'],
            ['name' => 'Erysipelas Vaccine', 'type' => 'vaccine', 'default_dosage' => '2 mL', 'withdrawal_days' => 0, 'notes' => 'Booster 3-4 weeks after first dose.'],
            ['name' => 'Ivermectin', 'type' => 'dewormer', 'default_dosage' => '1 mL', 'withdrawal_days' => 28, 'notes' => 'Injectable dewormer.'],
            ['name' => 'Oxytetracycline', 'type' => 'antibiotic', 'default_dosage' => '10 mL', 'withdrawal_days' => 21, 'notes' => 'Respiratory and enteric infections.'],
            ['name' => 'Vitamin B-Complex', 'type' => 'vitamin', 'default_dosage' => '5 mL', 'withdrawal_days' => 0, 'notes' => 'Appetite and stress support.'],
        ];

        foreach ($medications as $medication) {
            Medication::firstOrCreate(['name' => $medication['name']], $medication);
        }
    }
}
