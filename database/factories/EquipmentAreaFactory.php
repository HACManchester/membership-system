<?php

namespace Database\Factories;

use BB\Entities\EquipmentArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentArea>
 */
class EquipmentAreaFactory extends Factory
{
    protected $model = EquipmentArea::class;

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
