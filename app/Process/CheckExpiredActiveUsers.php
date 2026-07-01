<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\TelegramHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExpiredActiveUsers
{

    /**
     * How far past subscription_expires an active member may be before we act -
     * covers the BACS clearing lag between billing and the confirmation webhook.
     */
    const EXPIRY_TOLERANCE_DAYS = 7;

    /**
     * Active members with no expiry date at all are left alone this long after
     * signup, while their first payment is still working through GoCardless.
     */
    const NEW_MEMBER_GRACE_DAYS = 14;

    private $telegramHelper;

    public function __construct()
    {
        $this->telegramHelper = new TelegramHelper("CheckExpiredActiveUsers");
    }

    /**
     * Backstop for members whose billing failures never produced a GoCardless
     * webhook (e.g. the payment could not be created at all): the payment-warning
     * flow is otherwise entirely webhook-driven, so an active member whose
     * paid-up date has lapsed would keep access indefinitely.
     */
    public function run()
    {
        $today = new Carbon();
        $flaggedMembers = [];

        $users = User::where('status', 'active')->notSpecialCase()->get();
        foreach ($users as $user) {
            /** @var \BB\Entities\User $user */

            if ($user->subscription_expires) {
                $expired = $user->subscription_expires->lt($today->copy()->subDays(self::EXPIRY_TOLERANCE_DAYS));
            } else {
                $expired = $user->created_at && $user->created_at->lt($today->copy()->subDays(self::NEW_MEMBER_GRACE_DAYS));
            }

            if ($expired) {
                $lapsedDate = $user->subscription_expires ? $user->subscription_expires->format('Y-m-d') : 'never set';
                $user->setPaymentWarning();
                Log::info($user->name . ' was active with a lapsed membership (expired: ' . $lapsedDate . ') - moved to payment warning');
                array_push($flaggedMembers, $user->name);
            }
        }

        $message = "Expired active member check completed";
        if (!empty($flaggedMembers)) {
            $message .= " - Moved to payment warning: " . implode(", ", $flaggedMembers);
        }

        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB,
            $message
        );
    }

}
