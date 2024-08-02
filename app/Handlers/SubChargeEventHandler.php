<?php

namespace BB\Handlers;

use BB\Helpers\MembershipPayments;
use BB\Repo\UserRepository;
use Carbon\Carbon;

class SubChargeEventHandler
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param UserRepository               $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * A subscription charge has been marked as paid
     *
     * @param integer $chargeId
     * @param integer $userId
     * @param Carbon  $paymentDate
     * @param double  $amount
     */
    public function onPaid($chargeId, $userId, Carbon $paymentDate, $amount)
    {
        $user = $this->userRepository->getById($userId);
        
        $user->extendMembership(null, $paymentDate->addMonth());
    }

    /**
     * A subscription charge has been marked as processing
     *
     * @param integer $chargeId
     * @param integer $userId
     * @param Carbon  $paymentDate
     * @param double  $amount
     */
    public function onProcessing($chargeId, $userId, Carbon $paymentDate, $amount)
    {
        $user = $this->userRepository->getById($userId);

        $user->extendMembership(null, $paymentDate->addMonth());
    }

    /**
     * A sub charge has been rolled back as a payment failed
     *
     * @param integer $chargeId
     * @param integer $userId
     * @param Carbon  $paymentDate
     * @param double  $amount
     */
    public function onPaymentFailure($chargeId, $userId, Carbon $paymentDate, $amount)
    {
        $paidUntil = MembershipPayments::lastUserPaymentExpires($userId);

        $user = $this->userRepository->getById($userId);

        if ($paidUntil) {
            $user->extendMembership(null, $paidUntil);
        } else {
            $user->extendMembership(null, Carbon::now());
        }
    }
}
