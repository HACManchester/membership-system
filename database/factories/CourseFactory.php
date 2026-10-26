<?php

namespace Database\Factories;

use BB\Entities\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'format' => $this->faker->randomElement(['group', 'quiz', 'one-on-one']),
            'format_description' => $this->faker->sentence(),
            'frequency' => $this->faker->randomElement(['self-serve', 'regular', 'ad-hoc']),
            'frequency_description' => $this->faker->sentence(),
            'wait_time' => $this->faker->randomElement(['1 week', '1-2 weeks', '3-4 weeks', '5-6 weeks']),
            'live' => true,
        ];
    }
}
