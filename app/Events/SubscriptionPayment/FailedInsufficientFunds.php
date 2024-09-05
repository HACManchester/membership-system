<?php

namespace BB\Events\SubscriptionPayment;

use BB\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FailedInsufficientFunds extends Event
{
    use SerializesModels;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $subChargeId;

    /**
     * @param $userId
     * @param $subChargeId
     */
    public function __construct($userId, $subChargeId)
    {
        $this->userId = $userId;
        $this->subChargeId = $subChargeId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
