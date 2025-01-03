<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use BB\Entities\Payment;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'source' => $faker->randomElement([
            'gocardless',
            'gocardless-variable',
            'snackspace',
            'balance',
            'cash',
            'gift',
        ]),
        'source_id' => $faker->randomNumber(),
        'user_id' => $faker->randomNumber(),
        'amount' => $faker->randomFloat(2, 1, 1000),
        'fee' => $faker->randomFloat(2, 0, 100),
        'amount_minus_fee' => function (array $payment) {
            return $payment['amount'] - $payment['fee'];
        },
        'status' => $faker->randomElement([
            Payment::STATUS_PENDING,
            Payment::STATUS_PENDING_SUBMISSION,
            Payment::STATUS_CANCELLED,
            Payment::STATUS_PAID,
            Payment::STATUS_WITHDRAWN
        ]),
        'reason' => $faker->randomElement(array_keys(Payment::getPaymentReasons())),
        'created_at' => $faker->dateTime,
        'reference' => $faker->uuid,
        'paid_at' => $faker->dateTime,
    ];
});

$factory->state(Payment::class, 'pending', [
    'status' => Payment::STATUS_PENDING,
]);

$factory->state(Payment::class, 'pending_submission', [
    'status' => Payment::STATUS_PENDING_SUBMISSION,
]);

$factory->state(Payment::class, 'cancelled', [
    'status' => Payment::STATUS_CANCELLED,
]);

$factory->state(Payment::class, 'paid', [
    'status' => Payment::STATUS_PAID,
]);
$factory->state(Payment::class, 'fromCash', [
    'source' => 'cash',
]);

$factory->state(Payment::class, 'fromBalance', [
    'source' => 'balance',
]);