<?php

namespace BB\Http\Controllers;

use BB\Entities\Payment;
use BB\Entities\User;
use Exception;
use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Core\Exception\ValidationFailedException;

class GoCardlessPaymentController extends Controller
{
    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var \BB\Helpers\GoCardlessHelper
     */
    private $goCardless;

    function __construct(\BB\Repo\PaymentRepository $paymentRepository, \BB\Helpers\GoCardlessHelper $goCardless)
    {
        $this->paymentRepository = $paymentRepository;

        $this->middleware('role:member', array('only' => ['create', 'store']));
        $this->goCardless = $goCardless;
    }

    /**
     * Main entry point for all gocardless payments - not subscriptions
     * @param $userId
     * @return mixed
     * @throws \BB\Exceptions\AuthenticationException
     */
    public function create($userId)
    {
        $user = User::findWithPermission($userId);

        $requestData = \Request::only(['reason', 'amount', 'return_path']);

        $reason = $requestData['reason'];
        $amount = ($requestData['amount'] * 1) / 100;
        $returnPath = $requestData['return_path'];
        $ref = $this->getReference($reason);

        if (($user->payment_method == 'gocardless-variable') || ($user->secondary_payment_method == 'gocardless-variable')) {

            return $this->handleBill($amount, $reason, $user, $ref, $returnPath);
        } elseif ($user->payment_method == 'gocardless') {

            return $this->ddMigratePrompt($returnPath);
        } else {

            abort(500, 'Not supported');
        }
    }

    private function ddMigratePrompt($returnPath)
    {
        if (\Request::wantsJson()) {
            return \Response::json(['error' => 'Please visit the "Your Membership" page and migrate your Direct Debit first, then return and make the payment'], 400);
        }
        \FlashNotification::error("Please visit the \"Your Membership\" page and migrate your Direct Debit first, then return and make the payment");
        return \Redirect::to($returnPath);
    }

    /**
     * Process a direct debit payment when we have a preauth
     *
     * @param $amount
     * @param $reason
     * @param User $user
     * @param $ref
     * @param $returnPath
     * @return mixed
     */
    private function handleBill($amount, $reason, $user, $ref, $returnPath)
    {
        if (is_null($ref)) {
            $ref = '';
        }
        try {
            $bill = $this->goCardless->newBill($user->mandate_id, $amount * 100, $this->goCardless->getNameFromReason($reason));
            //Store the payment
            $fee = 0;
            $paymentSourceId = $bill->id;
            $amount = $bill->amount / 100;
            $status = $bill->status;
            if ($status == 'pending_submission') {
                $status = 'pending';
            }

            //The record payment process will make the necessary record updates
            $this->paymentRepository->recordPayment($reason, $user->id, 'gocardless-variable', $paymentSourceId, $amount, $status, $fee, $ref);

            if (\Request::wantsJson()) {
                return \Response::json(['message' => 'The payment was submitted successfully']);
            }

            \FlashNotification::success("The payment was submitted successfully");

            return \Redirect::to($returnPath);
        } catch (InvalidStateException | ValidationFailedException $e) {
            $status = 'failed';
            $this->paymentRepository->recordPayment($reason, $user->id, 'gocardless-variable', null, $amount, $status, 0, $ref);

            if (\Request::wantsJson()) {
                return \Response::json(['error' => 'We were unable to take payment from your account. Please try again.'], 400);
            }

            \FlashNotification::error("We were unable to take payment from your account. Please try again.");
            return \Redirect::to($returnPath);
        } catch (Exception $e) {
            // Genuine app exception... needs investigation
            \Log::info($e);

            $status = 'error';
            $this->paymentRepository->recordPayment($reason, $user->id, 'gocardless-variable', null, $amount, $status, 0, $ref);

            if (\Request::wantsJson()) {
                return \Response::json(['error' => 'We encountered an error taking your payment.'], 500);
            }

            \FlashNotification::error("We encountered an error taking your payment.");
            return \Redirect::to($returnPath);
        }
    }



    private function getDescription($reason)
    {
        if ($reason == 'subscription') {
            return "Monthly Subscription Fee - Manual";
        } elseif ($reason == 'induction') {
            return strtoupper(\Input::get('induction_key')) . " Induction Fee";
        } elseif ($reason == 'door-key') {
            return "Door Key Deposit";
        } elseif ($reason == 'storage-box') {
            return "Storage Box Deposit";
        } elseif ($reason == 'balance') {
            return "BB Credit Payment";
        } else {
            throw new \BB\Exceptions\NotImplementedException();
        }
    }

    private function getName($reason, $userId)
    {
        if ($reason == 'subscription') {
            return strtoupper("BBSUB" . $userId . ":MANUAL");
        } elseif ($reason == 'induction') {
            return strtoupper("BBINDUCTION" . $userId . ":" . \Request::get('induction_key'));
        } elseif ($reason == 'door-key') {
            return strtoupper("BBDOORKEY" . $userId);
        } elseif ($reason == 'storage-box') {
            return strtoupper("BBSTORAGEBOX" . $userId);
        } elseif ($reason == 'balance') {
            return strtoupper("BBBALANCE" . $userId);
        } else {
            throw new \BB\Exceptions\NotImplementedException();
        }
    }

    private function getReference($reason)
    {
        if ($reason == 'induction') {
            return \Request::get('ref');
        } elseif ($reason == 'balance') {
            return \Request::get('reference');
        }
        return false;
    }

    public function cancel(Payment $payment)
    {
        if ($payment->status != 'pending') {
            \FlashNotification::error("The payment could not be cancelled");
            return \Redirect::back();
        }

        $this->goCardless->cancelPayment($payment->source_id);
        \FlashNotification::success("Cancellation request sent to GoCardless");

        // The payment log will be updated from a webhook once GoCardless has actioned the cancellation

        return \Redirect::back();
    }
}
