<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\TelegramHelper;
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
            if ($user->subscription_expires === null || $user->subscription_expires->lt($today)) {
                // Suspend the member
                $user->setSuspended();
                Log::info($user->name . ' suspended - payment warning grace period expired');
                array_push($suspendedMembers, $user->name);
            } else {
                $daysLeft = $user->subscription_expires->diffInDays($today, false);
                Log::info($user->name . ' has payment warning, ' . abs($daysLeft) . ' days until suspension');
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