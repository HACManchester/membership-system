<?php

namespace Database\Factories;

use BB\Entities\KeyFob;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KeyFob>
 */
class KeyFobFactory extends Factory
{
    protected $model = KeyFob::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // Upper case: a key_id starting "ff" is an access code, shown differently
            'key_id' => Str::upper(Str::random(12)),
            'active' => 1,
            'lost' => 0,
        ];
    }
}
