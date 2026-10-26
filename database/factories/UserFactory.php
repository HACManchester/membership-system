<?php

namespace Database\Factories;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'given_name' => $this->faker->firstName(),
            'display_name' => $this->faker->firstName(),
            'family_name' => $this->faker->lastName(),
            'announce_name' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'password' => Str::random(10),
            'remember_token' => Str::random(10),
            'hash' => Str::random(32),
            'status' => 'active',
            'active' => true,
            'induction_completed' => false,
            'trusted' => false,
            'key_holder' => false,
            'phone' => false,
            'profile_private' => false,
        ];
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(Role::where('name', 'admin')->first());
        });
    }
}
