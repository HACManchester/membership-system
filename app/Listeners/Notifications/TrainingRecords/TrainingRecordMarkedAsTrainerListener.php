<?php

namespace BB\Listeners\Notifications\TrainingRecords;

use BB\Events\TrainingRecords\TrainingRecordMarkedAsTrainerEvent;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordMarkedAsTrainerNotification;
use BB\Repo\EquipmentRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrainingRecordMarkedAsTrainerListener
{
    /** @var EquipmentRepository */
    protected $equipmentRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(EquipmentRepository $equipmentRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
    }

    /**
     * Handle the event.
     *
     * @param  TrainingRecordMarkedAsTrainerEvent  $event
     * @return void
     */
    public function handle(TrainingRecordMarkedAsTrainerEvent $event)
    {
        // Load the course relationship
        $event->trainingRecord->load('course');
        
        $key = $event->trainingRecord->key;
        $equipment = $this->equipmentRepository->findByInductionCategory($key);

        $user = $event->trainingRecord->user;
        $user->notify(new TrainingRecordMarkedAsTrainerNotification($event->trainingRecord, $equipment));
    }
}
