<?php

namespace Database\Factories;

use BB\Entities\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'photos' => [],
            'obtained_at' => $this->faker->dateTimeThisYear(),
            'usage_cost_per' => 'hour',
        ];
    }

    public function requiresInduction(): static
    {
        return $this->state(fn (): array => [
            'requires_induction' => true,
            'induction_category' => $this->faker->word(),
            'accepting_inductions' => true,
        ]);
    }
}
