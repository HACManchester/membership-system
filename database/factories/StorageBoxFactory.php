<?php

use Faker\Generator as Faker;

$factory->define(BB\Entities\StorageBox::class, function (Faker $faker) {
    return [
        'user_id' => 0, // unclaimed
        'active' => true,
        'location' => "{$faker->randomElement(range(1, 100))} {$faker->randomElement(range('A', 'E'))}",
        'size' => 1,
    ];
});
