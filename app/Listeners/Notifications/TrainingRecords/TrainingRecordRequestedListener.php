<?php

namespace BB\Listeners\Notifications\TrainingRecords;

use BB\Entities\Equipment;
use BB\Entities\TrainingRecord;
use BB\Events\TrainingRecords\TrainingRecordRequestedEvent;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordRequestedNotification as InducteesInductionRequestedNotification;
use BB\Notifications\TrainingRecords\Trainers\TrainingRecordRequestedNotification as TrainersInductionRequestedNotification;
use BB\Repo\TrainingRecordRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class TrainingRecordRequestedListener
{
    /** @var TrainingRecordRepository */
    protected $trainingRecordRepository;

    public function __construct(TrainingRecordRepository $trainingRecordRepository)
    {
        $this->trainingRecordRepository = $trainingRecordRepository;
    }

    /**
     * Handle the event.
     *
     * @param  TrainingRecordRequestedEvent  $event
     * @return void
     */
    public function handle(TrainingRecordRequestedEvent $event)
    {
        // Load the course relationship
        $event->trainingRecord->load('course');
        
        $key = $event->trainingRecord->key;
        $equipment = Equipment::where('induction_category', $key)->get();

        $this->notifyInductee($event, $equipment);
        $this->notifyTrainers($event, $equipment);
    }

    protected function notifyInductee(TrainingRecordRequestedEvent $event, Collection $equipment)
    {
        $user = $event->trainingRecord->user;
        $user->notify(new InducteesInductionRequestedNotification($event->trainingRecord, $equipment));
    }

    protected function notifyTrainers(TrainingRecordRequestedEvent $event, Collection $equipment)
    {
        $record = $event->trainingRecord;
        // Course-based records match trainers via the course; legacy records via the key.
        $trainers = $record->course_id
            ? $this->trainingRecordRepository->getTrainersForCourse($record->course_id)
            : $this->trainingRecordRepository->getTrainersForKey($record->key);

        $trainers->each(function (TrainingRecord $trainer) use ($event, $equipment) {
            $trainer->user->notify(new TrainersInductionRequestedNotification($event->trainingRecord, $equipment));
        });
    }
}
