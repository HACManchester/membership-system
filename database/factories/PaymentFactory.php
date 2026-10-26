<?php

namespace Database\Factories;

use BB\Entities\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => $this->faker->randomElement([
                'gocardless',
                'gocardless-variable',
                'snackspace',
                'balance',
                'cash',
                'gift',
            ]),
            'source_id' => $this->faker->randomNumber(),
            'user_id' => $this->faker->randomNumber(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'fee' => $this->faker->randomFloat(2, 0, 100),
            'amount_minus_fee' => fn (array $payment) => $payment['amount'] - $payment['fee'],
            'status' => $this->faker->randomElement([
                Payment::STATUS_PENDING,
                Payment::STATUS_PENDING_SUBMISSION,
                Payment::STATUS_CANCELLED,
                Payment::STATUS_PAID,
                Payment::STATUS_WITHDRAWN,
            ]),
            'reason' => $this->faker->randomElement(array_keys(Payment::getPaymentReasons())),
            'created_at' => $this->faker->dateTime(),
            'reference' => $this->faker->uuid(),
            'paid_at' => $this->faker->dateTime(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => Payment::STATUS_PENDING]);
    }

    public function pendingSubmission(): static
    {
        return $this->state(['status' => Payment::STATUS_PENDING_SUBMISSION]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => Payment::STATUS_CANCELLED]);
    }

    public function paid(): static
    {
        return $this->state(['status' => Payment::STATUS_PAID]);
    }

    public function fromCash(): static
    {
        return $this->state(['source' => 'cash']);
    }

    public function fromBalance(): static
    {
        return $this->state(['source' => 'balance']);
    }
}
