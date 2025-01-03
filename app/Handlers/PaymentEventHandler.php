<?php namespace BB\Handlers;

use BB\Entities\Payment;
use BB\Entities\User;
use BB\Exceptions\PaymentException;
use BB\Repo\PaymentRepository;
use BB\Repo\SubscriptionChargeRepository;
use BB\Repo\UserRepository;
use Illuminate\Support\Facades\Log;

class PaymentEventHandler
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;
    
    /**
     * @var SubscriptionChargeRepository
     */
    private $subscriptionChargeRepository;

    /**
     * @param UserRepository               $userRepository
     * @param PaymentRepository            $paymentRepository
     * @param SubscriptionChargeRepository $subscriptionChargeRepository
     */
    public function __construct(UserRepository $userRepository, PaymentRepository $paymentRepository, SubscriptionChargeRepository $subscriptionChargeRepository)
    {
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepository;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
    }

    /**
     * New payment record is created
     *
     * @param $userId
     * @param $reason
     * @param $ref
     * @param $paymentId
     * @param $status
     */
    public function onCreate($userId, $reason, $ref, $paymentId, $status)
    {
        if ($reason == 'balance') {

            $this->updateBalance($userId);

        } elseif ($reason == 'subscription') {

            $this->updateSubPayment($paymentId, $userId, $status);

        } elseif ($reason == 'door-key') {

            $this->recordDoorKeyPaymentId($userId, $paymentId);

        } elseif ($reason == 'costs') {

            $this->updateBalance($userId);

        } else {
            
        }
    }

    /**
     * A payment has been deleted
     *
     * @param $userId
     * @param $source
     * @param $reason
     * @param $paymentId
     */
    public function onDelete($userId, $source, $reason, $paymentId)
    {
        if (($source == 'balance') || ($reason == 'balance')) {
            $this->updateBalance($userId);
        }

        if ($reason == 'storage-box') {

        }

        if ($reason == 'subscription') {

        }
    }

    /**
     * A payment has been cancelled
     *
     * @param $paymentId
     * @param $userId
     * @param $reason
     * @param $ref
     * @param $status
     */
    public function onCancel($paymentId, $userId, $reason, $ref, $status)
    {
        if ($reason == 'subscription') {
            if (empty($ref)) {
                Log::warning('Subscription payment failure, no sub charge id. Payment ID: ' . $paymentId);
                return;
            }
            $this->subscriptionChargeRepository->paymentFailed($ref);
        }
    }

    /**
     * A payment has been marked as paid
     *
     * @param $userId
     * @param $paymentId
     * @param $reason
     * @param $reference
     * @param $paymentDate
     */
    public function onPaid($userId, $paymentId, $reason, $reference, $paymentDate)
    {
        if (($reason == 'subscription') && $reference) {
            //For subscription payments the reference is the charge id
            $this->subscriptionChargeRepository->markChargeAsPaid($reference, $paymentDate);
        }
    }


    private function updateBalance($userId)
    {
        $memberCreditService = \App::make('\BB\Services\Credit');
        $memberCreditService->setUserId($userId);
        $memberCreditService->recalculate();
    }

    private function updateSubPayment($paymentId, $userId, $status)
    {
        /** @var Payment */
        $payment   = $this->paymentRepository->getById($paymentId);
        $subCharge = $this->subscriptionChargeRepository->findCharge($userId);

        if ( ! $subCharge) {
            Log::warning('Subscription payment without a sub charge. Payment ID:' . $paymentId);
            return;
        }

        //The sub charge record id gets saved onto the payment
        if (empty($payment->reference)) {
            $payment->reference = strval($subCharge->id);
            $payment->save();
        } else if ($payment->reference != $subCharge->id) {
            throw new PaymentException('Attempting to update sub charge (' . $subCharge->id . ') but payment (' . $payment->id . ') doesn\'t match. Sub charge has an existing reference on it.');
        }

        if ($status == 'paid') {
            $this->subscriptionChargeRepository->markChargeAsPaid($subCharge->id);
        } else if ($status == 'pending') {
            $this->subscriptionChargeRepository->markChargeAsProcessing($subCharge->id);
        }

        //The amount isn't stored on the sub charge record until its paid or processing
        if ($payment->amount != $subCharge->amount) {
            $this->subscriptionChargeRepository->updateAmount($subCharge->id, intval($payment->amount));
        }
    }

    private function recordDoorKeyPaymentId($userId, $paymentId)
    {
        /* @TODO: Verify payment amount is valid - this could have been changed */
        /** @var User */
        $user = $this->userRepository->getById($userId);
        $user->key_deposit_payment_id = $paymentId;
        $user->save();
    }


} 