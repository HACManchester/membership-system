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

$factory->define('BB\Entities\User', function (Generator $faker) {
    return [
        'given_name'          => $faker->firstName,
        'display_name'        => $faker->firstName,
        'family_name'         => $faker->lastName,
        'announce_name'       => $faker->lastName,
        'email'               => $faker->email,
        'password'            => Str::random(10),
        'remember_token'      => Str::random(10),
        'hash'                => Str::random(32),
        'status'              => 'active',
        'active'              => true,
        'induction_completed' => false,
        'trusted'             => false,
        'key_holder'          => false,
        'phone'               => false,
        'profile_private'     => false,
    ];
});

$factory->afterCreatingState(\BB\Entities\User::class, 'admin',  function (\BB\Entities\User $user, Generator $faker) {
    $admin = \BB\Entities\Role::where('name', 'admin')->first();
    $user->assignRole($admin);   
});

$factory->define('BB\Entities\ProfileData', function (Generator $faker) {
    return [
        'user_id'               => null,
        'profile_photo'         => false,
        'new_profile_photo'     => false,
        'profile_photo_private' => false,
        'profile_photo_on_wall' => false,
        'tagline'               => $faker->sentence,
    ];
});

$factory->define('BB\Entities\Role', function (Generator $faker) {
    return [
        'name'        => $faker->word,
        'title'       => $faker->word,
        'description' => $faker->sentence,
    ];
});

$factory->define('BB\Entities\KeyFob', function (Generator $faker) {
    return [
        'user_id' => 0,
        'key_id'  => str_random(12),
        'active'  => 1,
        'lost'    => 0,
    ];
});

$factory->define('BB\Entities\SubscriptionCharge', function (Generator $faker) {
    return [
        'user_id' => function () {
            return factory(\BB\Entities\User::class)->create()->id;
        },
        'charge_date' => $faker->dateTimeBetween('-1 month', 'now'),
        'payment_date' => $faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
        'amount' => $faker->randomElement([1700, 2200, 2700]), // Common subscription amounts in pence
        'status' => $faker->randomElement(['pending', 'due', 'processing', 'paid', 'cancelled']),
    ];
});
