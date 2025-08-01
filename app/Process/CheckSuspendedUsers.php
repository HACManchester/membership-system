<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\TelegramHelper;
use BB\Repo\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckSuspendedUsers
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    private $telegramHelper;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->telegramHelper = new TelegramHelper("CheckSuspendedUsers");
    }

    public function run()
    {

        $today = new Carbon();
        $members = [];

        // Fetch and check over active users which have a status of suspended
        $users = User::suspended()->get();
        foreach ($users as $user) {
            // Check if user has been suspended for 30+ days
            $suspendedFor30Days = $user->suspended_at && $user->suspended_at->lt($today->copy()->subDays(30));
            
            if ($suspendedFor30Days) {
                //User has been suspended for 30+ days, mark as left
                Log::info($user->name . ' has been suspended for 30+ days, marking as left');
                array_push($members, $user->name);
                $this->userRepository->memberLeft($user->id);
                //an email will be sent by the user observer
            } else {
                Log::info($user->name . ' is suspended but within 30-day period');
            }
        }

        $message = "Suspended members marked as left: " . implode(", ", $members);
        Log::info($message);
        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            $message
        );

    }

} 