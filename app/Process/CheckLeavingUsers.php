<?php namespace BB\Process;

use BB\Entities\User;
use BB\Repo\UserRepository;
use BB\Helpers\TelegramHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckLeavingUsers
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    private $telegramHelper;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->telegramHelper = new TelegramHelper("CheckLeavingUsers");
    }

    public function run()
    {

        $today = new Carbon();
        $members = [];

        //Fetch and check over active users which have a status of leaving
        $users = User::leaving()->notSpecialCase()->get();
        foreach ($users as $user) {
            if($user->subscription_expires){
                if ($user->subscription_expires->lt($today)) {
                    //User has passed their expiry date
                    
                    //set the status to left and active to false
                    $this->userRepository->memberLeft($user->id);
                    array_push($members, $user->name);

                    //an email will be sent by the user observer
                }
            }else{
                Log::error("User marked as active without an expiry date!");
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