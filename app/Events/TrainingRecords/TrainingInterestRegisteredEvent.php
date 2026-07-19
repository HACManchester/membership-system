<?php

namespace BB\Events\TrainingRecords;

use BB\Entities\TrainingRecord;
use BB\Events\Event;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a member joins a course's training waitlist. No listeners yet —
 * this is the integration point for the planned per-trainer notification
 * preferences (instant/digest), alongside TrainingRecordRequestedEvent.
 */
class TrainingInterestRegisteredEvent extends Event
{
    use SerializesModels;

    /**
     * @var TrainingRecord
     */
    public $trainingRecord;

    public function __construct(TrainingRecord $trainingRecord)
    {
        $this->trainingRecord = $trainingRecord;
    }
}
