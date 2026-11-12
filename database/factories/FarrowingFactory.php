<?php

namespace Database\Factories;

use App\Models\Breeding;
use App\Models\Farrowing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farrowing>
 */
class FarrowingFactory extends Factory
{
    protected $model = Farrowing::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::factory(),
            'breeding_id' => Breeding::factory()->farrowed(),
            'farrowing_date' => fake()->dateTimeBetween('-40 days', 'now')->format('Y-m-d'),
            'born_alive' => fake()->numberBetween(6, 14),
            'stillborn' => fake()->numberBetween(0, 2),
            'mummified' => fake()->numberBetween(0, 1),
            'notes' => null,
        ];
    }
}
