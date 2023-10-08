<?php

namespace BB\Listeners\Notifications\Inductions;

use BB\Events\Inductions\InductionMarkedAsTrainerEvent;
use BB\Notifications\Inductions\Inductees\InductionMarkedAsTrainerNotification;
use BB\Repo\EquipmentRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InductionMarkedAsTrainerListener
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
     * @param  InductionMarkedAsTrainerEvent  $event
     * @return void
     */
    public function handle(InductionMarkedAsTrainerEvent $event)
    {
        $inductionKey = $event->induction->key;
        $equipment = $this->equipmentRepository->findByInductionCategory($inductionKey);

        $user = $event->induction->user;
        $user->notify(new InductionMarkedAsTrainerNotification($event->induction, $equipment));
    }
}
