<?php

namespace BB\Http\Controllers;

use BB\Entities\Payment;
use BB\Entities\User;
use BB\Http\Requests\StoreGoCardlessPaymentRequest;
use BB\Support\PaymentStatus;
use Exception;
use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Core\Exception\ValidationFailedException;
use Illuminate\Support\Facades\Log;

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
     */
    public function create($userId, StoreGoCardlessPaymentRequest $request)
    {
        $user = $request->targetUser();

        $data = $request->validated();

        $reason = $data['reason'];
        $amount = (int) $data['amount'];

        if (($user->payment_method == 'gocardless-variable') || ($user->secondary_payment_method == 'gocardless-variable')) {

            return $this->handleBill($amount, $reason, $user);
        } elseif ($user->payment_method == 'gocardless') {

            return $this->ddMigratePrompt();
        } else {

            abort(500, 'Not supported');
        }
    }

    private function ddMigratePrompt()
    {
        return \Response::json(['error' => 'Please visit the "Your Membership" page and migrate your Direct Debit first, then return and make the payment'], 400);
    }

    /**
     * Process a direct debit payment when we have a preauth
     *
     * @param int $amount in pence
     * @param $reason
     * @param User $user
     * @return mixed
     */
    private function handleBill($amount, $reason, $user)
    {
        $ref = '';

        try {
            $bill = $this->goCardless->newBill($user->mandate_id, $amount, $this->goCardless->getNameFromReason($reason));
            //Store the payment
            $fee = 0;
            $paymentSourceId = $bill->id;
            $amount = $bill->amount;
            $status = PaymentStatus::fromGoCardless($bill->status, PaymentStatus::Pending);
            if ($status === PaymentStatus::PendingSubmission) {
                $status = PaymentStatus::Pending;
            }

            //The record payment process will make the necessary record updates
            $this->paymentRepository->recordPayment($reason, $user->id, 'gocardless-variable', $paymentSourceId, $amount, $status, $fee, $ref);

            return \Response::json(['message' => 'The payment was submitted successfully']);
        } catch (InvalidStateException | ValidationFailedException $e) {
            $status = PaymentStatus::Failed;
            $this->paymentRepository->recordPayment($reason, $user->id, 'gocardless-variable', null, $amount, $status, 0, $ref);

            return \Response::json(['error' => 'We were unable to take payment from your account. Please try again.'], 400);
        } catch (Exception $e) {
            // Genuine app exception... needs investigation
            Log::info($e);

            $status = PaymentStatus::Error;
            $this->paymentRepository->recordPayment($reason, $user->id, 'gocardless-variable', null, $amount, $status, 0, $ref);

            return \Response::json(['error' => 'We encountered an error taking your payment.'], 500);
        }
    }

    public function cancel(Payment $payment)
    {
        if ($payment->status != PaymentStatus::Pending) {
            \FlashNotification::error("The payment could not be cancelled");
            return \Redirect::back();
        }

        $this->goCardless->cancelPayment($payment->source_id);
        \FlashNotification::success("Cancellation request sent to GoCardless");

        // The payment log will be updated from a webhook once GoCardless has actioned the cancellation

        return \Redirect::back();
    }
}
