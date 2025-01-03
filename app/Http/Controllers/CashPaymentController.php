<?php namespace BB\Http\Controllers;

use BB\Entities\User;
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
     *
     * @param $userId
     */
    public function store($userId, Request $request)
    {
        User::findWithPermission($userId);

        $this->validateWithBag('credit', $request, [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required',
            'source_id' => 'required',
            'return_path' => 'required',
        ]);

        $amount     = $request->get('amount');
        $reason     = $request->get('reason');
        $sourceId   = $request->get('source_id');
        $returnPath = $request->get('return_path');

        $sourceId = $sourceId . ':' . time();
        
        $this->paymentRepository->recordPayment($reason, $userId, 'cash', $sourceId, $amount);

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
     * @param $userId
     * @return mixed
     * @throws \BB\Exceptions\AuthenticationException
     * @throws \BB\Exceptions\InvalidDataException
     */
    public function destroy($userId, Request $request)
    {
        $user = User::findWithPermission($userId);
        $this->bbCredit->setUserId($userId);

        $minimumBalance = $this->bbCredit->acceptableNegativeBalance('withdrawal');
        $maxWithdrawal = ($user->cash_balance / 100) + $minimumBalance;
        
        $this->validateWithBag('withdrawal', $request, [
            'amount' => "required|numeric|min:0|max:{$maxWithdrawal}",
            'ref' => 'required',
            'return_path' => 'required',
        ]);

        $amount     = $request->get('amount');
        $ref = $request->get('ref');
        $returnPath = $request->get('return_path');
        
        $this->paymentRepository->recordPayment('withdrawal', $userId, 'balance', '', $amount, 'paid', 0, $ref);

        $this->bbCredit->recalculate();

        \FlashNotification::success("Payment recorded");
        $returnPath_balance = '/balance';
        $result = $returnPath . $returnPath_balance;

        return \Redirect::to($result);
    }
}
