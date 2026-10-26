<?php

namespace Database\Factories;

use BB\Entities\ProfileData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileData>
 */
class ProfileDataFactory extends Factory
{
    protected $model = ProfileData::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'profile_photo' => false,
            'new_profile_photo' => false,
            'profile_photo_private' => false,
            'profile_photo_on_wall' => false,
            'tagline' => $this->faker->sentence(),
        ];
    }
}
