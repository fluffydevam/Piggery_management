<?php

namespace Database\Factories;

use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pig>
 */
class PigFactory extends Factory
{
    protected $model = Pig::class;

    public function definition(): array
    {
        $type = fake()->randomElement([Pig::TYPE_SOW, Pig::TYPE_GILT, Pig::TYPE_BOAR]);

        return [
            'tag_number' => 'PIG-'.fake()->unique()->numerify('####'),
            'type' => $type,
            'breed' => fake()->randomElement(['Landrace', 'Large White', 'Duroc', 'Berkshire', 'Pietrain']),
            'sex' => $type === Pig::TYPE_BOAR ? 'male' : 'female',
            'birth_date' => fake()->dateTimeBetween('-3 years', '-4 months')->format('Y-m-d'),
            'cage_id' => null,
            'status' => Pig::STATUS_ACTIVE,
            'origin' => fake()->randomElement(['bred_on_farm', 'purchased']),
            'notes' => null,
        ];
    }

    public function sow(): static
    {
        return $this->state(fn () => ['type' => Pig::TYPE_SOW, 'sex' => 'female']);
    }

    public function boar(): static
    {
        return $this->state(fn () => ['type' => Pig::TYPE_BOAR, 'sex' => 'male']);
    }

    public function gilt(): static
    {
        return $this->state(fn () => ['type' => Pig::TYPE_GILT, 'sex' => 'female']);
    }
}
