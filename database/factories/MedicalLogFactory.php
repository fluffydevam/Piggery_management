<?php

namespace Database\Factories;

use App\Models\MedicalLog;
use App\Models\Medication;
use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalLog>
 */
class MedicalLogFactory extends Factory
{
    protected $model = MedicalLog::class;

    public function definition(): array
    {
        return [
            'treatable_type' => Pig::class,
            'treatable_id' => Pig::factory(),
            'medication_id' => Medication::factory(),
            'type' => MedicalLog::TYPE_VITAMIN,
            'date_administered' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'dosage' => '2 mL',
            'route' => 'im',
            'administered_by' => 'Farm owner',
            'next_due_date' => null,
            'completed_at' => null,
            'notes' => null,
        ];
    }
}
