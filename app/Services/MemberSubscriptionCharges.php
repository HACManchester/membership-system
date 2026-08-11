<?php namespace BB\Services;

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

    /**
     * @var TelegramHelper
     */
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
     * @return array{created: string[], failed: string[]} names of members a charge was created or failed for
     */
    public function createSubscriptionCharges($targetDate)
    {
        $created = [];
        $failed = [];

        $users = $this->userRepository->getBillableActive();
        foreach ($users as $user) {
            // Being active isn't enough. Cancelling clears the payment method but leaves
            // active = true until the daily check retires the member, so without this we
            // keep raising charges against someone with no way to pay - which is how the
            // backlog built up in the first place. Same test the billing run applies, so
            // the two can't disagree about who is a paying member
            if ( ! $this->canBeBilled($user)) {
                continue;
            }

            if (($user->payment_day == $targetDate->day) && ( ! $this->subscriptionChargeRepository->chargeExists($user->id, $targetDate))) {
                try {
                    $this->subscriptionChargeRepository->createCharge($user->id, $targetDate, $user->monthly_subscription);
                    $created[] = $user->name;
                } catch (Exception $e) {
                    // One member's bad data must not stop charge creation for everyone after them
                    $failed[] = $user->name;
                    Log::error('Failed to create sub charge for user ' . $user->id . ' (' . date_format($targetDate, 'Y-m-d') . ')');
                    Log::error($e);
                }
            }
        }

        return ['created' => $created, 'failed' => $failed];
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

        [$unbillable, $subCharges] = $subCharges->partition(function ($charge) {
            return ! $this->canBeBilled($charge->user);
        });
        $this->cancelUnbillableCharges($unbillable);

        //Filter the list into two gocardless and balance subscriptions
        $goCardlessUsers = $subCharges->filter(function ($charge) {
            return $charge->user->payment_method == 'gocardless-variable';
        });

        // Only the direct debits, because only they are collected automatically. A cash
        // or standing order member's charge sits due until someone records the payment,
        // and being slow about that is not a billing fault to alert on every night.
        [$stale, $goCardlessUsers] = $goCardlessUsers->partition(function ($charge) {
            return $charge->charge_date->lt(Carbon::now()->subDays($this->maxChargeAgeDays()));
        });
        $this->reportStaleCharges($stale);

        //Charge the gocardless users
        $members = [];
        $membersWeCouldntBill = [];
        foreach ($goCardlessUsers as $charge) {
            $amount = $charge->amount > 0 ? $charge->amount : $charge->user->monthly_subscription;
            $bill = null;
            try {
                try {
                    $bill = $this->goCardless->newBill($charge->user->mandate_id, ($amount * 100), $this->goCardless->getNameFromReason('subscription'));
                    $status = $bill->status;
                    if ($status == 'pending_submission') {
                        $status = 'pending';
                    }
                }
                catch (InvalidStateException | ValidationFailedException $e) {
                    $status = 'failed';
                }
                catch (Exception $e) {
                    $status = 'error';
                }

                $paymentId = $this->paymentRepository->recordSubscriptionPayment($charge->user->id, 'gocardless-variable', $bill->id ?? null, $amount, $status, 0, $charge->id);

                if ($bill) {
                    $members[] = $charge->user->name;
                } else {
                    $membersWeCouldntBill[] = $charge->user->name;
                }

                if ($status == 'failed') {
                    // GoCardless rejected the payment outright, so no failure webhook will
                    // ever arrive; run the same path one would trigger (cancel the charge
                    // and put the member into payment-warning)
                    $this->paymentRepository->recordPaymentFailure($paymentId, 'failed');
                }
            }
            catch (Exception $e) {
                // One member's bad data must not abort billing for everyone after them
                $membersWeCouldntBill[] = $charge->user->name;
                Log::error('Billing failed for user ' . $charge->user->id . ' (sub charge ' . $charge->id . ')');
                Log::error($e);
            }
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
     * Should we be taking money from this member at all?
     *
     * A member with no payment method is the state a cancellation leaves behind, and an
     * inactive one has been suspended or has left. Either way their charges can't be
     * collected - but nothing used to say so. They were dropped from the billing run
     * without a word and picked up again, months of them, the moment the member set up
     * a new mandate. Members paying by cash or standing order do keep a payment method:
     * their charges legitimately wait here to be settled by hand.
     *
     * @param \BB\Entities\User|null $user
     * @return bool
     */
    public function canBeBilled($user)
    {
        if ( ! $user || ! $user->active || empty($user->payment_method)) {
            return false;
        }

        return ! ($user->payment_method == 'gocardless-variable' && empty($user->mandate_id));
    }

    /**
     * @return int Days after its charge date that a due charge stops being collectable
     */
    private function maxChargeAgeDays()
    {
        return (int) config('membership.billing.max_charge_age_days');
    }

    /**
     * @param \Illuminate\Support\Collection $charges
     */
    private function cancelUnbillableCharges($charges)
    {
        if ($charges->isEmpty()) {
            return;
        }

        $members = [];
        foreach ($charges as $charge) {
            $this->subscriptionChargeRepository->cancelCharge($charge->id);
            $members[] = $charge->user ? $charge->user->name : 'user ' . $charge->user_id;
        }

        $message = "Cancelled " . $charges->count() . " uncollectable sub charges (member has no payment method): " . implode(", ", array_unique($members));
        Log::warning($message);
        $this->telegramHelper->notify(
            TelegramHelper::WARNING,
            $message
        );
    }

    /**
     * Charges this old are left alone rather than cancelled - the money may well still
     * be owed, but collecting a backdated month automatically is never right. Someone
     * needs to look at why it sat here.
     *
     * @param \Illuminate\Support\Collection $charges
     */
    private function reportStaleCharges($charges)
    {
        if ($charges->isEmpty()) {
            return;
        }

        $details = $charges->map(function ($charge) {
            $name = $charge->user ? $charge->user->name : 'user ' . $charge->user_id;
            return $name . ' (' . $charge->charge_date->format('Y-m-d') . ')';
        })->unique()->implode(", ");

        $message = "Not billing " . $charges->count() . " sub charges over " . $this->maxChargeAgeDays() . " days old - these need review: " . $details;
        Log::warning($message);
        $this->telegramHelper->notify(
            TelegramHelper::WARNING,
            $message
        );
    }

}
