<?php namespace BB\Http\Controllers;

use BB\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashPaymentController extends Controller
{
    /** Pages a cash payment form can ask to return to */
    private const RETURN_PAGES = ['account', 'balance'];



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
            'amount' => 'required|numeric|min:0',
            'reason' => 'required',
            'source_id' => 'required',
            'return_to' => ['nullable', Rule::in(self::RETURN_PAGES)],
        ]);

        $amount     = $request->get('amount');
        $reason     = $request->get('reason');
        $sourceId   = $request->get('source_id');

        $sourceId = $sourceId . ':' . time();
        
        $this->paymentRepository->recordPayment($reason, $user->id, 'cash', $sourceId, $amount);

        if (\Request::wantsJson()) {
            return \Response::json(['message' => 'Topup Successful']);
        }

        return redirect()->to($this->returnUrl($request->input('return_to'), $user))
            ->with('success', 'Top Up successful');
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

        $minimumBalance = $this->bbCredit->acceptableNegativeBalance('withdrawal');
        $maxWithdrawal = ($user->cash_balance / 100) + $minimumBalance;
        
        $this->validateWithBag('withdrawal', $request, [
            'amount' => "required|numeric|min:0|max:{$maxWithdrawal}",
            'ref' => 'required',
            'return_to' => ['nullable', Rule::in(self::RETURN_PAGES)],
        ]);

        $amount     = $request->get('amount');
        $ref = $request->get('ref');
        
        $this->paymentRepository->recordPayment('withdrawal', $user->id, 'balance', '', $amount, \BB\Support\PaymentStatus::Paid, 0, $ref);

        $this->bbCredit->recalculate();

        return redirect()->to($this->returnUrl($request->input('return_to'), $user))
            ->with('success', 'Payment recorded');
    }

    /**
     * The page the form came from, named by a token rather than a URL so it can't
     * send anyone off-site; anything else goes to the member's balance page.
     */
    private function returnUrl(?string $returnTo, User $user): string
    {
        return match ($returnTo) {
            'account' => route('account.show', $user->id),
            default => route('account.balance.index', $user->id),
        };
    }
}
