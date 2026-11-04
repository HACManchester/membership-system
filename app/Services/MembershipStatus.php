<?php

namespace BB\Services;

use BB\Entities\User;
use BB\Repo\SubscriptionChargeRepository;
use BB\Support\MemberStatus;
use BB\Support\SubscriptionChargeStatus;
use Carbon\Carbon;
use DateTimeInterface;

/**
 * Moves a member between membership statuses (see the state machine in
 * docs/billing.md). UserObserver reacts to the status changes these save,
 * sending the emails, Telegram notices and events.
 */
class MembershipStatus
{
    public function __construct(private SubscriptionChargeRepository $subscriptionChargeRepository)
    {
    }

    /**
     * The member has set up a payment method, so make them active. If nothing
     * covers them yet, raise this month's charge, and with a variable direct
     * debit collect it straight away.
     *
     * Reloads the member first, since callers usually record the new payment
     * method through UserRepository just before.
     */
    public function activate(User $user): void
    {
        $user->refresh();

        $user->active = true;
        $user->status = MemberStatus::Active;
        $user->suspended_at = null;
        $user->save();

        $outstandingCharges = $this->subscriptionChargeRepository->hasOutstandingCharges($user->id);

        //If the user doesn't have any charges currently processing or they dont have an expiry date or are past their expiry data create a charge
        if (!$outstandingCharges && (!$user->subscription_expires || $user->subscription_expires->lt(Carbon::now()))) {
            $chargeDate = Carbon::now();

            //If we are passed the end of month cutoff push the charge date forward to their actual charge date
            if ((Carbon::now()->day > 28) && $user->payment_day === 1) {
                $chargeDate = $chargeDate->day(1)->addMonth();
            }

            if ($user->payment_method == 'gocardless-variable') {
                $this->subscriptionChargeRepository->createChargeAndBillDD($user->id, $chargeDate, $user->monthly_subscription, SubscriptionChargeStatus::Due, $user->mandate_id);
            } else {
                // This will create the monthly sub charge but not take any money, that will happen tomorrow during the normal run
                $this->subscriptionChargeRepository->createCharge($user->id, $chargeDate, $user->monthly_subscription, SubscriptionChargeStatus::Due);
            }
        }
    }

    /**
     * A payment has covered the member until $expiry (a month from now by
     * default). Banned members are left as they are.
     */
    public function extend(User $user, ?string $paymentMethod = null, ?DateTimeInterface $expiry = null): void
    {
        if ($user->banned) {
            return;
        }

        if (empty($expiry)) {
            $expiry = Carbon::now()->addMonth();
        }
        $user->status = MemberStatus::Active;
        $user->active = true;
        if ($paymentMethod) {
            $user->payment_method = $paymentMethod;
        }
        $user->gift = '';
        $user->subscription_expires = Carbon::instance($expiry);
        $user->suspended_at = null;
        $user->save();
    }

    /**
     * A payment has failed. The member keeps access for the grace period, after
     * which CheckPaymentWarnings suspends them.
     */
    public function setPaymentWarning(User $user, int $gracePeriodDays = 10): void
    {
        $user->status = MemberStatus::PaymentWarning;
        $user->active = true; // Keep space access during warning period

        // Set subscription_expires to failure date + grace period for fair timing
        // TODO: Articles of association say 2 weeks from payment falling due
        $user->subscription_expires = Carbon::now()->addDays($gracePeriodDays);
        $user->save();
    }

    public function suspend(User $user): void
    {
        $user->status = MemberStatus::Suspended;
        $user->active = false;
        $user->suspended_at = Carbon::now();
        $user->save();
    }

    /**
     * The member has asked to leave; they keep access until their membership
     * expires, when CheckLeavingUsers marks them as left.
     */
    public function markLeaving(User $user): void
    {
        $user->status = MemberStatus::Leaving;
        $user->save();
    }

    /**
     * The member's payment method has gone away - either they cancelled it here or
     * their bank cancelled the mandate and GoCardless told us about it.
     *
     * Outstanding charges have to go with it. They can never be collected while the
     * member has no payment method, so they sit at 'due' accumulating one a month,
     * invisible, until the member sets up a new mandate - at which point the whole
     * backlog is billed against it in a single run.
     */
    public function cancelSubscription(User $user): void
    {
        $user->refresh();

        $user->payment_method = '';
        $user->subscription_id = '';
        $user->mandate_id = '';
        $user->status = MemberStatus::Leaving;
        $user->save();

        $this->subscriptionChargeRepository->cancelOutstandingCharges($user->id);
    }

    /**
     * The member has left: disable their account and cancel any outstanding charges.
     */
    public function markLeft(User $user): void
    {
        $user->refresh();

        $user->active = false;
        $user->status = MemberStatus::Left;
        $user->save();

        $this->subscriptionChargeRepository->cancelOutstandingCharges($user->id);
    }
}
