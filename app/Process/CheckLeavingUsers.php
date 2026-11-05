<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\TelegramHelper;
use BB\Services\MembershipStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckLeavingUsers
{

    private $telegramHelper;

    public function __construct(private MembershipStatus $membershipStatus)
    {
        $this->telegramHelper = new TelegramHelper("CheckLeavingUsers");
    }

    public function run()
    {

        $today = new Carbon();
        $members = [];

        //Fetch and check over active users which have a status of leaving
        $users = User::leaving()->notSpecialCase()->get();
        foreach ($users as $user) {
            if ($user->subscription_expires) {
                if ($user->subscription_expires->lt($today)) {
                    //User has passed their expiry date
                    
                    //set the status to left and active to false
                    $this->membershipStatus->markLeft($user);
                    array_push($members, $user->name);

                    //an email will be sent by the user observer
                }
            }else{
                // Nothing left to wait for: no paid-up period to run down and no payment
                // method to collect one with. Left here they stay active indefinitely and
                // the nightly run keeps raising charges nothing can collect
                Log::warning($user->name . ' was leaving with no expiry date - marking as left');
                $this->membershipStatus->markLeft($user);
                array_push($members, $user->name);
            }

        }

        $message = "Members set as left: " . implode(", ", $members);
        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            $message
        );
    }

} 