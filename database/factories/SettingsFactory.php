<?php

namespace Database\Factories;

use BB\Entities\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Settings>
 */
class SettingsFactory extends Factory
{
    protected $model = Settings::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->lexify('setting_????????'),
            'value' => $this->faker->numerify('####'),
        ];
    }
}
