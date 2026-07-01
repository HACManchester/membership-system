<?php namespace BB\Handlers;

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Repo\PaymentRepository;
use BB\Repo\SubscriptionChargeRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class PaymentEventHandler
{
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;
    
    /**
     * @var SubscriptionChargeRepository
     */
    private $subscriptionChargeRepository;

    /**
     * @param PaymentRepository            $paymentRepository
     * @param SubscriptionChargeRepository $subscriptionChargeRepository
     */
    public function __construct(PaymentRepository $paymentRepository, SubscriptionChargeRepository $subscriptionChargeRepository)
    {
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
            // Still in use by admin topups/withdrawals.
            $this->updateBalance($userId);
        } elseif ($reason == 'subscription') {
            $this->updateSubPayment($paymentId, $userId, $status);
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
        if ($reason == 'balance') {
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
        $payment = $this->paymentRepository->getById($paymentId);

        if ( ! empty($payment->reference)) {
            // The payment was recorded against a specific charge - always use that one;
            // findCharge() would return the user's oldest outstanding charge instead,
            // which is a different charge for anyone with a payment failure history
            try {
                /** @var SubscriptionCharge $subCharge */
                $subCharge = $this->subscriptionChargeRepository->getById($payment->reference);
            } catch (ModelNotFoundException $e) {
                Log::warning('Subscription payment references a missing sub charge. Payment ID: ' . $paymentId);
                return;
            }
        } else {
            if (in_array($status, ['failed', 'error', 'cancelled'])) {
                // A dead payment must not attach itself to a live charge: the nightly
                // biller skips any charge that has a payment against it, so linking
                // here would permanently block the charge from being collected
                return;
            }

            $subCharge = $this->subscriptionChargeRepository->findCharge($userId);

            if ( ! $subCharge) {
                Log::warning('Subscription payment without a sub charge. Payment ID:' . $paymentId);
                return;
            }

            //The sub charge record id gets saved onto the payment
            $payment->reference = strval($subCharge->id);
            $payment->save();
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
} 