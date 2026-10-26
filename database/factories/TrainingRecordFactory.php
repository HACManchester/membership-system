<?php

namespace Database\Factories;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A member's request for training on a course, not yet signed off.
 *
 * @extends Factory<TrainingRecord>
 */
class TrainingRecordFactory extends Factory
{
    protected $model = TrainingRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            // The legacy key still holds the course's slug
            'key' => fn (array $record) => Course::find($record['course_id'])?->slug ?? $this->faker->slug(),
        ];
    }

    public function trained(): static
    {
        return $this->state(fn (): array => [
            'trained' => now(),
            'trainer_user_id' => User::factory(),
        ]);
    }

    public function trainer(): static
    {
        return $this->trained()->state(['is_trainer' => true]);
    }
}
