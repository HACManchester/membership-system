<?php

namespace Database\Factories;

use BB\Entities\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
