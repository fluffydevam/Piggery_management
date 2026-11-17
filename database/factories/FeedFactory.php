<?php

namespace Database\Factories;

use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feed>
 */
class FeedFactory extends Factory
{
    protected $model = Feed::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::factory(),
            'name' => ucfirst(fake()->unique()->words(2, true)).' Feed',
            'category' => fake()->randomElement(Feed::CATEGORIES),
            'bag_weight_kg' => 50,
            'stock_kg' => 0,
            'reorder_level_kg' => 100,
            'unit_cost' => fake()->randomFloat(2, 900, 2500),
            'notes' => null,
        ];
    }
}
