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
        $user = $this->userRepository->getById($userId);

        // Only set payment warning if they don't have other payments for this charge
        if ($this->hasOtherPaymentsForCharge($chargeId)) {
            return; // Don't set payment warning if other payments exist for this charge
        }

        if ($user->status === "active") {
            $user->setPaymentWarning();
        }
    }

    private function hasOtherPaymentsForCharge($chargeId)
    {
        $paymentRepository = app('BB\Repo\PaymentRepository');
        
        // Check for any other payments linked to this subscription charge
        $otherPayments = $paymentRepository->getPaymentsByReference($chargeId)
            ->whereIn('status', ['paid', 'pending', 'pending_submission', 'processing'])
            ->count();
            
        return $otherPayments > 0;
    }
}
