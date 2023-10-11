<?php

namespace BB\Events\Inductions;

use BB\Entities\Induction;
use BB\Events\Event;
use Illuminate\Queue\SerializesModels;

class InductionCompletedEvent extends Event
{
    use SerializesModels;

    /**
     * @var Induction
     */
    public $induction;

    /**
     * Create a new event instance.
     *
     * @param Induction $induction
     */
    public function __construct(Induction $induction)
    {
        $this->induction = $induction;
    }
}
