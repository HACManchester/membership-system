<?php

namespace Database\Factories;

use BB\Entities\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api_token' => Str::random(40),
            'description' => $this->faker->sentence(),
        ];
    }
}
