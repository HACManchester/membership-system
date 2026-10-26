<?php

namespace Database\Factories;

use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionCharge>
 */
class SubscriptionChargeFactory extends Factory
{
    protected $model = SubscriptionCharge::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'charge_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payment_date' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'amount' => $this->faker->randomElement([1700, 2200, 2700]), // Common subscription amounts in pence
            'status' => $this->faker->randomElement(['pending', 'due', 'processing', 'paid', 'cancelled']),
        ];
    }
}
