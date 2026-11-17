<?php

namespace Database\Factories;

use App\Models\Farrowing;
use App\Models\Litter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Litter>
 */
class LitterFactory extends Factory
{
    protected $model = Litter::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::factory(),
            'farrowing_id' => Farrowing::factory(),
            'cage_id' => null,
            'litter_tag' => 'LIT-'.fake()->unique()->numerify('####'),
            'status' => Litter::STATUS_NURSING,
            'current_count' => fake()->numberBetween(5, 12),
            'deaths_count' => 0,
            'weaned_date' => null,
            'weaned_count' => null,
            'notes' => null,
        ];
    }
}
