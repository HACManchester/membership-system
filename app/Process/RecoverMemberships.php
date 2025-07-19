<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Helpers\TelegramHelper;
use Illuminate\Support\Facades\Log;

class RecoverMemberships
{
    /**
     * @var TelegramHelper
     */
    private $telegramHelper;

    public function __construct()
    {
        $this->telegramHelper = new TelegramHelper("RecoverMemberships");
    }

    public function run()
    {
        $recoveredMembers = [];
        
        // Check all users for payment recovery opportunities
        // This includes active, payment-warning, and suspended users
        $users = User::whereIn('status', ['active', 'payment-warning', 'suspended'])
            ->notSpecialCase()
            ->get();
            
        foreach ($users as $user) {
            /** @var \BB\Entities\User $user */
            
            // Check if they have valid payments that should extend their membership
            $paidUntil = MembershipPayments::lastUserPaymentExpires($user->id);
            
            if ($paidUntil && ($user->subscription_expires === null || $user->subscription_expires->lt($paidUntil))) {
                // They have a valid payment that extends beyond their current expiry
                $previousStatus = $user->status;
                $user->extendMembership($user->payment_method, $paidUntil);
                
                Log::info($user->name . ' recovered from ' . $previousStatus . ' - membership extended to ' . $paidUntil->format('Y-m-d'));
                array_push($recoveredMembers, $user->name . ' (' . $previousStatus . ')');
            }
        }

        $message = "Membership recovery check completed";
        if (!empty($recoveredMembers)) {
            $message .= " - Recovered: " . implode(", ", $recoveredMembers);
        } else {
            $message .= " - No memberships recovered";
        }
        
        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            $message
        );
    }
}