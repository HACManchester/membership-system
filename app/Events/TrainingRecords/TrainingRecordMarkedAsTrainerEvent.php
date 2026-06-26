<?php

namespace BB\Events\TrainingRecords;

use BB\Entities\TrainingRecord;
use BB\Events\Event;
use Illuminate\Queue\SerializesModels;

class TrainingRecordMarkedAsTrainerEvent extends Event
{
    use SerializesModels;

    /**
     * @var TrainingRecord
     */
    public $trainingRecord;

    /**
     * Create a new event instance.
     *
     * @param TrainingRecord $trainingRecord
     */
    public function __construct(TrainingRecord $trainingRecord)
    {
        $this->trainingRecord = $trainingRecord;
    }
}
