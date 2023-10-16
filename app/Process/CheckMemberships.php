<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Helpers\TelegramHelper;
use BB\Services\MemberSubscriptionCharges;

/**
 * Loop through each member and look at their last subscription payment
 *   If its over a month ago (plus a grace period) mark them as having a payment warning
 * Class CheckMemberships
 * @package BB\Process
 */
class CheckMemberships
{

    /**
     * @var MemberSubscriptionCharges
     */
    private $memberSubscriptionCharges;
    private $telegramHelper;

    public function __construct(MemberSubscriptionCharges $memberSubscriptionCharges)
    {
        $this->memberSubscriptionCharges = $memberSubscriptionCharges;
        $this->telegramHelper = new TelegramHelper("CheckMemberships");
    }

    public function run()
    {
        $users = User::active()->notSpecialCase()->get();
        $members = [];

        foreach ($users as $user) {
            /** @var $user \BB\Entities\User */
            echo $user->name;
            $expired = false;

            $cutOffDate = MembershipPayments::getSubGracePeriodDate($user->payment_method);
            if ( ! $user->subscription_expires || $user->subscription_expires->lt($cutOffDate)) {
                // TODO: Send email warning members who've fallen within the grace period?
                $expired = true;
            }

            //Check for payments first
            if ($expired) {
                $paidUntil = MembershipPayments::lastUserPaymentExpires($user->id);
                //$paidUntil = $this->memberSubscriptionCharges->lastUserChargeExpires($user->id);
                if ($paidUntil) {
                    if ($user->subscription_expires && $user->subscription_expires->lt($paidUntil)) {
                        $user->extendMembership($user->payment_method, $paidUntil);

                        //This may not be true but it simplifies things now and tomorrows process will deal with it
                        $expired = false;
                    }
                }
            }
            if ($expired) {
                $user->setSuspended();
                echo ' - Suspended';
                array_push($members, $user->name);
            }


            echo PHP_EOL;
        }

        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            "Checked Memberships - set suspended: " . implode(", ", $members)
        );

    }
} 