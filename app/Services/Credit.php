<?php namespace BB\Services;

use BB\Entities\User;
use BB\Exceptions\InvalidDataException;
use BB\Exceptions\NotImplementedException;
use BB\Helpers\MembershipPayments;
use BB\Repo\PaymentRepository;
use BB\Repo\UserRepository;

class Credit
{

    private $userId;
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    private $user;

    /**
     * @param PaymentRepository $paymentRepository
     * @param UserRepository    $userRepository
     */
    public function __construct(PaymentRepository $paymentRepository, UserRepository $userRepository)
    {
        $this->paymentRepository = $paymentRepository;
        $this->userRepository = $userRepository;
    }


    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        $this->user = $this->userRepository->getById($this->userId);
    }


    public function recalculate()
    {
        if (! $this->user instanceof User) {
            throw new InvalidDataException("User not set");
        }
        $runningTotal = 0;
        $positivePayments = $this->paymentRepository->getUserPaymentsByReason($this->userId, 'balance');
        $negativePayments = $this->paymentRepository->getUserPaymentsBySource($this->userId, 'balance');

        foreach ($positivePayments as $payment) {
            $runningTotal = $runningTotal + $payment->amount;
        }
        foreach ($negativePayments as $payment) {
            $runningTotal = $runningTotal - $payment->amount;
        }
        $this->user->cash_balance = $runningTotal;
        $this->user->save();
    }

    /**
     * Can the user spend money they don't have and if so how much?
     * @param $reason
     * @return int in pence
     */
    public function acceptableNegativeBalance($reason)
    {
        switch ($reason) {
            case 'storage-box':
                return 2000;
            case 'equipment-fee':
                return 2000;
            default:
                return 2000;
        }
    }

    public function getBalanceSign(): string
    {
        return $this->user->cash_balance >= 0 ? 'positive' : 'negative';
    }

    public function getBalanceFormatted(): string
    {
        return MembershipPayments::formatSignedPrice($this->user->cash_balance);
    }

    public function getBalancePaymentsPaginated()
    {
        return $this->paymentRepository->getBalancePaymentsPaginated($this->user->id);
    }


} 