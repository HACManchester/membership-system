<?php

namespace BB\Listeners\Notifications\TrainingRecords;

use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordCompletedNotification;
use BB\Entities\Equipment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrainingRecordCompletedListener
{
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
        $equipment = Equipment::where('induction_category', $key)->get();

        $user = $event->trainingRecord->user;
        $user->notify(new TrainingRecordCompletedNotification($event->trainingRecord, $equipment));
    }
}
