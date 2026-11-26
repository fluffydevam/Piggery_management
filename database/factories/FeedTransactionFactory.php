<?php

namespace Database\Factories;

use App\Models\Feed;
use App\Models\FeedTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedTransaction>
 */
class FeedTransactionFactory extends Factory
{
    protected $model = FeedTransaction::class;

    public function definition(): array
    {
        return [
            'feed_id' => Feed::factory(),
            'type' => FeedTransaction::TYPE_IN,
            'quantity_bags' => fake()->randomFloat(2, 1, 10),
            'transaction_date' => fake()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'reference' => null,
            'cost' => null,
            'user_id' => fn () => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::factory(),
            'notes' => null,
        ];
    }
}
