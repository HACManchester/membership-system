<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use BB\Entities\Course;
use Faker\Generator as Faker;

$factory->define(Course::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'slug' => $faker->slug,
        'description' => $faker->paragraph,
        'format' => $faker->randomElement(['group', 'quiz', 'one-on-one']),
        'format_description' => $faker->sentence,
        'frequency' => $faker->randomElement(['self-serve', 'regular', 'ad-hoc']),
        'frequency_description' => $faker->sentence,
        'wait_time' => $faker->randomElement(['1 week', '1-2 weeks', '3-4 weeks', '5-6 weeks']),
        'live' => true,
    ];
});
