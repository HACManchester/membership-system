<?php

namespace Database\Factories;

use BB\Entities\MaintainerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintainerGroup>
 */
class MaintainerGroupFactory extends Factory
{
    protected $model = MaintainerGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
        ];
    }
}
