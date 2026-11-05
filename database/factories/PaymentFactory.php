<?php

namespace Database\Factories;

use BB\Entities\Payment;
use BB\Support\PaymentStatus;
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
                PaymentStatus::Pending,
                PaymentStatus::PendingSubmission,
                PaymentStatus::Cancelled,
                PaymentStatus::Paid,
                PaymentStatus::Withdrawn,
            ]),
            'reason' => $this->faker->randomElement(array_keys(Payment::getPaymentReasons())),
            'created_at' => $this->faker->dateTime(),
            'reference' => $this->faker->uuid(),
            'paid_at' => $this->faker->dateTime(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => PaymentStatus::Pending]);
    }

    public function pendingSubmission(): static
    {
        return $this->state(['status' => PaymentStatus::PendingSubmission]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => PaymentStatus::Cancelled]);
    }

    public function paid(): static
    {
        return $this->state(['status' => PaymentStatus::Paid]);
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
