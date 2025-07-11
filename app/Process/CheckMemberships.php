<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Helpers\TelegramHelper;
use BB\Services\MemberSubscriptionCharges;
use Illuminate\Support\Facades\Log;

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
        $leftMembers = [];

        // Process suspended users for transition to left after 30 days
        $suspendedUsers = User::suspended()->get();
        foreach ($suspendedUsers as $user) {
            /** @var \BB\Entities\User $user */
            echo $user->name;
            
            // Check if they've been suspended for 30 days
            if ($user->suspended_at && $user->suspended_at->lt(\Carbon\Carbon::now()->subDays(30))) {
                // Mark as left
                $user->status = 'left';
                $user->active = false;
                $user->save();
                echo ' - Marked as left (30 days since suspension)';
                array_push($leftMembers, $user->name);
            } else {
                echo ' - Still suspended';
            }
            echo PHP_EOL;
        }

        // Process active users for recovery (extend membership if valid payments found)
        $activeUsers = User::active()->notSpecialCase()->get();
        $recoveredMembers = [];
        
        foreach ($activeUsers as $user) {
            /** @var \BB\Entities\User $user */
            echo $user->name;
            
            // Check if they have valid payments that should extend their membership
            $cutOffDate = MembershipPayments::getSubGracePeriodDate($user->payment_method);
            $needsExtension = !$user->subscription_expires || $user->subscription_expires->lt($cutOffDate);
            
            if ($needsExtension) {
                $paidUntil = MembershipPayments::lastUserPaymentExpires($user->id);
                if ($paidUntil && $user->subscription_expires && $user->subscription_expires->lt($paidUntil)) {
                    $user->extendMembership($user->payment_method, $paidUntil);
                    echo ' - Membership extended';
                    array_push($recoveredMembers, $user->name);
                }
            }
            echo PHP_EOL;
        }

        $message = "Checked Memberships";
        if (!empty($leftMembers)) {
            $message .= " - Marked as left: " . implode(", ", $leftMembers);
        }
        if (!empty($recoveredMembers)) {
            $message .= " - Recovered: " . implode(", ", $recoveredMembers);
        }
        
        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            $message
        );
    }
} 