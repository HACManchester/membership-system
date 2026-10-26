<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/** @var Factory $factory */

$factory->define('BB\Entities\SubscriptionCharge', function (Generator $faker) {
    return [
        'user_id' => function () {
            return \BB\Entities\User::factory()->create()->id;
        },
        'charge_date' => $faker->dateTimeBetween('-1 month', 'now'),
        'payment_date' => $faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
        'amount' => $faker->randomElement([1700, 2200, 2700]), // Common subscription amounts in pence
        'status' => $faker->randomElement(['pending', 'due', 'processing', 'paid', 'cancelled']),
    ];
});
