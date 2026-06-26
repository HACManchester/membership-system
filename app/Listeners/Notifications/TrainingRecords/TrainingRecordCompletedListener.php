<?php

namespace BB\Listeners\Notifications\TrainingRecords;

use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordCompletedNotification;
use BB\Repo\EquipmentRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrainingRecordCompletedListener
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
     * @param  TrainingRecordCompletedEvent  $event
     * @return void
     */
    public function handle(TrainingRecordCompletedEvent $event)
    {
        // Load the course relationship
        $event->trainingRecord->load('course');
        
        $key = $event->trainingRecord->key;
        $equipment = $this->equipmentRepository->findByInductionCategory($key);

        $user = $event->trainingRecord->user;
        $user->notify(new TrainingRecordCompletedNotification($event->trainingRecord, $equipment));
    }
}
