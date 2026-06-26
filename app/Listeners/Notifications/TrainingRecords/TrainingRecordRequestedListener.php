<?php

namespace BB\Listeners\Notifications\TrainingRecords;

use BB\Entities\TrainingRecord;
use BB\Events\TrainingRecords\TrainingRecordRequestedEvent;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordRequestedNotification as InducteesInductionRequestedNotification;
use BB\Notifications\TrainingRecords\Trainers\TrainingRecordRequestedNotification as TrainersInductionRequestedNotification;
use BB\Repo\EquipmentRepository;
use BB\Repo\TrainingRecordRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class TrainingRecordRequestedListener
{
    /** @var EquipmentRepository */
    protected $equipmentRepository;

    /** @var TrainingRecordRepository */
    protected $trainingRecordRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(EquipmentRepository $equipmentRepository, TrainingRecordRepository $trainingRecordRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
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
        $equipment = $this->equipmentRepository->findByInductionCategory($key);

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
        $key = $event->trainingRecord->key;
        $trainers = $this->trainingRecordRepository->getTrainersForEquipment($key);

        $trainers->each(function (TrainingRecord $trainer) use ($event, $equipment) {
            $trainer->user->notify(new TrainersInductionRequestedNotification($event->trainingRecord, $equipment));
        });
    }
}
