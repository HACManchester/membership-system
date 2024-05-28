<?php

namespace BB\Listeners;

use BB\Events\MemberBalanceChanged;
use BB\Events\PaymentCancelled;
use BB\Services\Credit;

class MemberBalanceSubscriber
{
    /**
     * @var Credit
     */
    private $memberCreditService;

    /**
     * Create the event listener.
     *
     * @param Credit $memberCreditService
     */
    public function __construct(Credit $memberCreditService)
    {
        $this->memberCreditService = $memberCreditService;
    }

    public function subscribe($events)
    {
        $events->listen(
            MemberBalanceChanged::class,
            'BB\Listeners\MemberBalanceSubscriber@handleMemberBalanceChanged'
        );
        $events->listen(
            PaymentCancelled::class,
            'BB\Listeners\MemberBalanceSubscriber@handlePaymentCancelled'
        );
    }

    public function handleMemberBalanceChanged(MemberBalanceChanged $event)
    {
        $this->memberCreditService->setUserId($event->userId);
        $this->memberCreditService->recalculate();
    }

    public function handlePaymentCancelled(PaymentCancelled $event)
    {
        $user_id = $event->payment->user_id;
        $this->memberCreditService->setUserId($user_id);
        $this->memberCreditService->recalculate();
    }
}
