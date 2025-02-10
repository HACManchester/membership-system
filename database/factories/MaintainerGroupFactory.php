<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use BB\Entities\MaintainerGroup;
use Faker\Generator as Faker;

$factory->define(MaintainerGroup::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
        'slug' => $faker->unique()->slug,
        'description' => $faker->sentence,
    ];
});
