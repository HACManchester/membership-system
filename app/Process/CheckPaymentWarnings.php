<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Helpers\TelegramHelper;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckPaymentWarnings
{

    /**
     * @var TelegramHelper
     */
    private $telegramHelper;

    public function __construct()
    {
        $this->telegramHelper = new TelegramHelper("CheckPaymentWarnings");
    }

    public function run()
    {
        $today = new Carbon();
        $warningMembers = [];
        $suspendedMembers = [];

        // Process users in payment-warning status
        $users = User::paymentWarning()->get();
        foreach ($users as $user) {
            /** @var \BB\Entities\User $user */
            
            // Check if grace period has expired (subscription_expires is set to failure date + grace period)
            $shouldSuspend = !$user->subscription_expires || $user->subscription_expires->lt($today);
            
            if ($shouldSuspend) {
                // Check if they have valid payments that should extend them (recovery logic)
                $paidUntil = MembershipPayments::lastUserPaymentExpires($user->id);
                if ($paidUntil && (!$user->subscription_expires || $user->subscription_expires->lt($paidUntil))) {
                    // They have valid payment, extend membership and keep active
                    $user->extendMembership($user->payment_method, $paidUntil);
                    $shouldSuspend = false;
                    echo $user->name . ' payment warning resolved - membership extended' . PHP_EOL;
                }
            }
            
            if ($shouldSuspend) {
                // Suspend the member
                $user->setSuspended();
                echo $user->name . ' suspended - payment warning grace period expired' . PHP_EOL;
                array_push($suspendedMembers, $user->name);
            } else {
                $daysLeft = $user->subscription_expires ? $user->subscription_expires->diffInDays($today, false) : 0;
                echo $user->name . ' has payment warning, ' . abs($daysLeft) . ' days until suspension' . PHP_EOL;
                array_push($warningMembers, $user->name);
            }
        }

        $message = "Payment warnings processed";
        if (!empty($warningMembers)) {
            $message .= " - Active warnings: " . implode(", ", $warningMembers);
        }
        if (!empty($suspendedMembers)) {
            $message .= " - Suspended: " . implode(", ", $suspendedMembers);
        }
        
        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            $message
        );
    }

} 