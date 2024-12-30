<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use BB\Entities\Equipment;
use Faker\Generator as Faker;

$factory->define(Equipment::class, function (Faker $faker) {
    $name = $faker->unique()->word;
    $slug = Str::slug($name);

    return [
        'name' => $name,
        // 'manufacturer' => '',
        // 'model_number' => '',
        // 'serial_number' => '',
        // 'colour' => '',
        // 'location' => '',
        // 'room' => '',
        // 'detail' => '',
        'slug' => $slug,
        // 'description' => '',
        // 'help_text' => '',
        // 'managing_role_id' => '',
        // 'requires_induction' => '',
        // 'induction_category' => '',
        // 'working' => '',
        // 'permaloan' => '',
        // 'permaloan_user_id' => '',
        // 'access_fee' => '',
        'photos' => [], // Not sure why this can be omitted when not creating via factories?
        // 'archive' => '',
        'obtained_at' => $faker->dateTimeThisYear,
        // 'removed_at' => '',
        // 'asset_tag_id' => '',
        // 'usage_cost' => '',
        'usage_cost_per' => 'hour',
        // 'ppe' => '',
        // 'dangerous' => '',
        // 'induction_instructions' => '',
        // 'trainer_instructions' => '',
        // 'trained_instructions' => '',
        // 'docs' => '',
        // 'access_code' => '',
        // 'accepting_inductions' => '',
    ];
});

$factory->state(Equipment::class, 'requiresInduction', function (Faker $faker) {
    return [
        'requires_induction'  => true,
        'induction_category'  => $faker->word,
        'accepting_inductions' => true,
    ];
});
