<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'type' => fake()->randomElement(Medication::TYPES),
            'default_dosage' => fake()->randomElement(['1 mL', '2 mL', '5 mL', '10 mL']),
            'withdrawal_days' => fake()->numberBetween(0, 28),
            'notes' => null,
        ];
    }
}
