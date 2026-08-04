<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use BB\Entities\Room;
use Faker\Generator as Faker;

$factory->define(Room::class, function (Faker $faker) {
    $name = $faker->unique()->words(2, true);

    return [
        'name' => ucfirst($name),
        'slug' => Str::slug($name),
        'description' => $faker->optional()->sentence,
    ];
});
