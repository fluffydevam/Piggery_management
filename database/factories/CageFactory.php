<?php

namespace Database\Factories;

use App\Models\Cage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cage>
 */
class CageFactory extends Factory
{
    protected $model = Cage::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::factory(),
            'cage_number' => 'C-'.fake()->unique()->numerify('###'),
            'type' => fake()->randomElement(Cage::TYPES),
            'capacity' => fake()->numberBetween(1, 10),
            'location' => fake()->randomElement(['Barn A', 'Barn B', 'Open shed']),
            'status' => Cage::STATUS_AVAILABLE,
            'notes' => null,
        ];
    }
}
