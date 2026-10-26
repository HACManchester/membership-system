<?php

namespace Database\Factories;

use BB\Entities\AccessLockdown;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A running lockdown, started by an admin, that lets only admins in.
 *
 * @extends Factory<AccessLockdown>
 */
class AccessLockdownFactory extends Factory
{
    protected $model = AccessLockdown::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'started_by' => User::factory()->admin(),
            'reason' => $this->faker->sentence(),
            'roles' => ['admin'],
        ];
    }

    public function lifted(): static
    {
        return $this->state(fn (): array => [
            'lifted_at' => now(),
            'lifted_by' => User::factory()->admin(),
        ]);
    }
}
