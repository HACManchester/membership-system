<?php

namespace BB\Events\TrainingRecords;

use BB\Entities\Course;
use BB\Entities\User;
use BB\Events\Event;

/**
 * Fired when a member leaves a course's training waitlist (self-withdrawal or
 * trainer removal). Carries the user and course rather than the training
 * record — the record is deleted, and serializing a deleted model would break
 * queued listeners. No listeners yet; integration point for the planned
 * per-trainer notification preferences.
 */
class TrainingInterestWithdrawnEvent extends Event
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Course
     */
    public $course;

    public function __construct(User $user, Course $course)
    {
        $this->user = $user;
        $this->course = $course;
    }
}
