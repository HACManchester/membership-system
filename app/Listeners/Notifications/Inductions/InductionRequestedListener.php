<?php

namespace BB\Listeners\Notifications\Inductions;

use BB\Entities\Induction;
use BB\Events\Inductions\InductionRequestedEvent;
use BB\Notifications\Inductions\Inductees\InductionRequestedNotification as InducteesInductionRequestedNotification;
use BB\Notifications\Inductions\Trainers\InductionRequestedNotification as TrainersInductionRequestedNotification;
use BB\Repo\EquipmentRepository;
use BB\Repo\InductionRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class InductionRequestedListener
{
    /** @var EquipmentRepository */
    protected $equipmentRepository;

    /** @var InductionRepository */
    protected $inductionRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(EquipmentRepository $equipmentRepository, InductionRepository $inductionRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
        $this->inductionRepository = $inductionRepository;
    }

    /**
     * Handle the event.
     *
     * @param  InductionRequestedEvent  $event
     * @return void
     */
    public function handle(InductionRequestedEvent $event)
    {
        // Load the course relationship
        $event->induction->load('course');
        
        $inductionKey = $event->induction->key;
        $equipment = $this->equipmentRepository->findByInductionCategory($inductionKey);

        $this->notifyInductee($event, $equipment);
        $this->notifyTrainers($event, $equipment);
    }

    protected function notifyInductee(InductionRequestedEvent $event, Collection $equipment)
    {
        $user = $event->induction->user;
        $user->notify(new InducteesInductionRequestedNotification($event->induction, $equipment));
    }

    protected function notifyTrainers(InductionRequestedEvent $event, Collection $equipment)
    {
        $inductionKey = $event->induction->key;
        $trainers = $this->inductionRepository->getTrainersForEquipment($inductionKey);

        $trainers->each(function (Induction $trainer) use ($event, $equipment) {
            $trainer->user->notify(new TrainersInductionRequestedNotification($event->induction, $equipment));
        });
    }
}
