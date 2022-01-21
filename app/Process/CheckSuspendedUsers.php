<?php namespace BB\Process;

use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Helpers\TelegramHelper;
use BB\Repo\UserRepository;
use Carbon\Carbon;

class CheckSuspendedUsers
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    private $telegramHelper;

    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->telegramHelper = new TelegramHelper("CheckSuspendedUsers");
    }

    public function run()
    {

        $today = new Carbon();
        $members = [];

        //Fetch and check over active users which have a status of suspended
        $users = User::suspended()->get();
        foreach ($users as $user) {
            if ( ! $user->subscription_expires || $user->subscription_expires->lt($today)) {
                //User has passed their expiry date
                echo $user->name . ' is suspended and has passed their expiry date' . PHP_EOL;
                
                //Check the actual expiry date
                
                //When did their last sub payment expire
                $paidUntil = MembershipPayments::lastUserPaymentExpires($user->id);
                
                if ($paidUntil) {
                    if ( ! $user->subscription_expires || $user->subscription_expires->lt($paidUntil)) {
                        $user->extendMembership(null, $paidUntil);
                        continue;
                    }
                }
                
                array_push($members, $user->name);
                $this->userRepository->memberLeft($user->id);

                //an email will be sent by the user observer
            } else {
                echo $user->name . ' has a payment warning but is within their expiry date' . PHP_EOL;
            }
        }

        $this->telegramHelper->notify(
            TelegramHelper::JOB, 
            "Suspended members marked as left: " . implode(", ", $members)
        );

    }

} 