<?php namespace BB\Services;

use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Helpers\TelegramHelper;
use BB\Repo\PaymentRepository;
use BB\Repo\SubscriptionChargeRepository;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Exception;
use GoCardlessPro\Core\Exception\InvalidStateException;
use GoCardlessPro\Core\Exception\ValidationFailedException;
use Illuminate\Support\Facades\Log;

class MemberSubscriptionCharges
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var SubscriptionChargeRepository
     */
    private $subscriptionChargeRepository;
    /**
     * @var GoCardlessHelper
     */
    private $goCardless;
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    private $telegramHelper;

    function __construct(UserRepository $userRepository, SubscriptionChargeRepository $subscriptionChargeRepository, GoCardlessHelper $goCardless, PaymentRepository $paymentRepository)
    {
        $this->userRepository = $userRepository;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->goCardless = $goCardless;
        $this->paymentRepository = $paymentRepository;
        $this->telegramHelper = new TelegramHelper("createSubscriptionCharges");
    }

    /**
     * Create the sub charge for each member, only do this for members with dates matching the supplied date
     *
     * @param Carbon $targetDate
     */
    public function createSubscriptionCharges($targetDate)
    {
        try {
            $users = $this->userRepository->getBillableActive();
            foreach ($users as $user) {
                if (($user->payment_day == $targetDate->day) && ( ! $this->subscriptionChargeRepository->chargeExists($user->id, $targetDate))) {
                    $this->subscriptionChargeRepository->createCharge($user->id, $targetDate, $user->monthly_subscription);
                }
            }

            $message = "Charges ran for " . date_format($targetDate,"Y-m-d");
            Log::info($message);
            $this->telegramHelper->notify(
                TelegramHelper::JOB, 
                $message
            );
        }
        catch(Exception $e) {
            $message = "Exception running" . date_format($targetDate,"Y-m-d");
            Log::error($message);
            $this->telegramHelper->notify(
                TelegramHelper::ERROR, 
                $message
            );
            Log::error($e);
        }
    }

    /**
     * Locate all charges that are for today or the past and mark them as due
     */
    public function makeChargesDue()
    {
        $subCharges = $this->subscriptionChargeRepository->getPending();
        foreach ($subCharges as $charge) {
            if ($charge->charge_date->isToday() || $charge->charge_date->isPast()) {
                $this->subscriptionChargeRepository->setDue($charge->id);
            }
        }
    }

    /**
     * Bill members based on the sub charges that are due
     */
    public function billMembers()
    {
        $subCharges = $this->subscriptionChargeRepository->getDue();

        //Check each of the due charges, if they have previous attempted payments ignore them
        // we don't want to retry failed payments as for DD's this will generate bank charges
        $subCharges = $subCharges->reject(function ($charge) {
            return $this->paymentRepository->getPaymentsByReference($charge->id)->count() > 0;
        });

        //Filter the list into two gocardless and balance subscriptions
        $goCardlessUsers = $subCharges->filter(function ($charge) {
            return $charge->user->payment_method == 'gocardless-variable';
        });

        //Charge the gocardless users
        $members = [];
        $membersWeCouldntBill = [];
        foreach ($goCardlessUsers as $charge) {
            $amount = $charge->amount > 0 ? $charge->amount : $charge->user->monthly_subscription;
            try {
                $bill = $this->goCardless->newBill($charge->user->mandate_id, ($amount * 100), $this->goCardless->getNameFromReason('subscription'));
                $members[] = $charge->user->name;
                $status = $bill->status;
                if ($status == 'pending_submission') {
                    $status = 'pending';
                }
            }
            catch (InvalidStateException | ValidationFailedException $e) {
                // TODO: Notify member somehow? If not, they'll eventually be picked up by CheckMemberships
                $status = 'failed';
                $membersWeCouldntBill[] = $charge->user->name;
            }
            catch (Exception $e) {
                $status = 'error';
                $membersWeCouldntBill[] = $charge->user->name;
            }

            $this->paymentRepository->recordSubscriptionPayment($charge->user->id, 'gocardless-variable', $bill->id ?? null, $amount, $status, 0, $charge->id);
        };

        $message = "Created bills for: " . implode(", ", $members);
        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB,
            $message
        );

        if (count($membersWeCouldntBill) > 0) {
            $message = "Could not create bills for: " . implode(", ", $membersWeCouldntBill);
            Log::info($message);
            $this->telegramHelper->notify(
                TelegramHelper::JOB,
                $message
            );
        }
    }


    /**
     * Get a users latest sub payment
     * @param $userId
     * @return bool
     */
    public function lastUserChargeExpires($userId)
    {
        $charge = User::where('user_id', $userId)->where('status', ['processing', 'paid'])->orderBy('charge_date', 'DESC')->first();
        if ($charge) {
            return $charge->charge_date->addMonth();
        }
        return false;
    }
}
