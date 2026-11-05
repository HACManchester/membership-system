<?php namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use Illuminate\Http\Request;

class CashPaymentController extends Controller
{


    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var \BB\Services\Credit
     */
    private $bbCredit;

    function __construct(\BB\Repo\PaymentRepository $paymentRepository, \BB\Services\Credit $bbCredit)
    {
        $this->paymentRepository = $paymentRepository;
        $this->bbCredit = $bbCredit;
    }


    /**
     * Start the creation of a new gocardless payment
     *   Details get posted into this method and the redirected to gocardless
     */
    public function store(string $userId, Request $request)
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);

        $this->validateWithBag('credit', $request, [
            'amount' => 'required|numeric|decimal:0,2|min:0|max:1000',
            'reason' => 'required',
            'source_id' => 'required',
            'return_path' => 'required',
        ], [
            'amount.max' => 'The most we can credit at once is £1,000.',
        ]);

        // The form takes pounds; payments are in pence
        $amount     = (int) round($request->get('amount') * 100);
        $reason     = $request->get('reason');
        $sourceId   = $request->get('source_id');
        $returnPath = $request->get('return_path');

        $sourceId = $sourceId . ':' . time();
        
        $this->paymentRepository->recordPayment($reason, $user->id, 'cash', $sourceId, $amount);

        \FlashNotification::success("Top Up successful");

        $returnPath_balance = '/balance?confetti=1';
        $result = $returnPath . $returnPath_balance;

        if (\Request::wantsJson()) {
            return \Response::json(['message' => 'Topup Successful']);
        }

        \FlashNotification::error("Success");
        
        return \Redirect::to($result);

   
    }

    /**
     * Remove cash from the users balance
     *
     * @throws \BB\Exceptions\InvalidDataException
     */
    public function destroy(string $userId, Request $request)
    {
        $user = User::findOrFail($userId);
        $this->authorize('manage', $user);
        $this->bbCredit->setUserId($user->id);

        // The form takes pounds; balances and payments are in pence
        $maxWithdrawal = MembershipPayments::formatPounds($user->cash_balance + $this->bbCredit->acceptableNegativeBalance('withdrawal'));

        $this->validateWithBag('withdrawal', $request, [
            'amount' => "required|numeric|decimal:0,2|min:0|max:{$maxWithdrawal}",
            'ref' => 'required',
            'return_path' => 'required',
        ]);

        $amount     = (int) round($request->get('amount') * 100);
        $ref = $request->get('ref');
        $returnPath = $request->get('return_path');
        
        $this->paymentRepository->recordPayment('withdrawal', $user->id, 'balance', '', $amount, \BB\Support\PaymentStatus::Paid, 0, $ref);

        $this->bbCredit->recalculate();

        \FlashNotification::success("Payment recorded");
        $returnPath_balance = '/balance';
        $result = $returnPath . $returnPath_balance;

        return \Redirect::to($result);
    }
}
