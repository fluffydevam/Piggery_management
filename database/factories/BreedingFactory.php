<?php

namespace Database\Factories;

use App\Models\Breeding;
use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Breeding>
 */
class BreedingFactory extends Factory
{
    protected $model = Breeding::class;

    public function definition(): array
    {
        return [
            'sow_id' => Pig::factory()->sow(),
            'boar_id' => Pig::factory()->boar(),
            'breeding_date' => fake()->dateTimeBetween('-110 days', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(['natural', 'ai']),
            'semen_source' => null,
            'pregnancy_status' => Breeding::STATUS_PENDING,
            'confirmed_pregnant_at' => null,
            'notes' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'pregnancy_status' => Breeding::STATUS_CONFIRMED,
            'confirmed_pregnant_at' => today()->subDays(90)->toDateString(),
        ]);
    }

    public function farrowed(): static
    {
        return $this->state(fn () => ['pregnancy_status' => Breeding::STATUS_FARROWED]);
    }
}
