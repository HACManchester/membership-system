<?php

namespace BB\Listeners\Notifications\Inductions;

use BB\Events\Inductions\InductionCompletedEvent;
use BB\Notifications\Inductions\Inductees\InductionCompletedNotification;
use BB\Repo\EquipmentRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InductionCompletedListener
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
     * @param  InductionCompletedEvent  $event
     * @return void
     */
    public function handle(InductionCompletedEvent $event)
    {
        $inductionKey = $event->induction->key;
        $equipment = $this->equipmentRepository->findByInductionCategory($inductionKey);

        $user = $event->induction->user;
        $user->notify(new InductionCompletedNotification($event->induction, $equipment));
    }
}
