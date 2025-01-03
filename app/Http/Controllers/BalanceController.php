<?php namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Exceptions\AuthenticationException;
use BB\Exceptions\ValidationException;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;
    /**
     * @var \BB\Services\Credit
     */
    private $bbCredit;

    public function __construct(\BB\Repo\UserRepository $userRepository, \BB\Services\Credit $bbCredit)
    {
        $this->userRepository = $userRepository;
        $this->bbCredit = $bbCredit;
    }

    public function index($userId)
    {
        //Verify the user can access this user record
        $user = User::findWithPermission($userId);
        $this->bbCredit->setUserId($user->id);

        $userBalance = $this->bbCredit->getBalanceFormatted();
        $userBalanceSign = $this->bbCredit->getBalanceSign();

        $payments = $this->bbCredit->getBalancePaymentsPaginated();

        $memberList = $this->userRepository->getAllAsDropdown();

        return \View::make('account.bbcredit.index')
            ->with('user', $user)
            ->with('confetti', \Request::get('confetti'))
            ->with('payments', $payments)
            ->with('userBalance', $userBalance)
            ->with('userBalanceSign', $userBalanceSign)
            ->with('rawBalance', number_format($user->cash_balance / 100, 2))
            ->with('memberList', $memberList);
    }
} 