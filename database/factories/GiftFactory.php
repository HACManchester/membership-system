<?php

namespace Database\Factories;

use BB\Entities\Gift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gift>
 */
class GiftFactory extends Factory
{
    protected $model = Gift::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(12)),
            'gifter_name' => $this->faker->name(),
            'giftee_name' => $this->faker->name(),
            'months' => $this->faker->randomElement([1, 3, 6, 12]),
            // Pounds, unlike the member's cash_balance
            'credit' => $this->faker->randomElement([0, 5, 10, 20]),
            'expires' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ];
    }
}
