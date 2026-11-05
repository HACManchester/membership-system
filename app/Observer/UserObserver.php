<?php namespace BB\Observer;

use BB\Events\MemberBecameActive;
use BB\Events\MemberBecameInactive;
use BB\Events\MemberDiscourseParamsChanged;
use BB\Mailer\UserMailer;
use BB\Helpers\TelegramHelper;
use BB\Support\MemberStatus;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function updating($user){
        $original = $user->getOriginal();
        
        // If they changed their email, require reconfirmation
        if ($original['email'] != $user->email){
            $user->emailChanging();
            $this->sendConfirmationEmail($user);
        }
    }

    /**
     * Look at the user record each time its saved and fire events
     * @param $user
     */
    public function saved($user)
    {
        $original = $user->getOriginal();

        //Use status changed from setting-up to something else
        if (($original['status'] == MemberStatus::SettingUp) && ($user->status != MemberStatus::SettingUp)) {
            $this->newUser($user);
        }

        //User status changed to payment warning
        if (($original['status'] != MemberStatus::PaymentWarning) && ($user->status == MemberStatus::PaymentWarning)) {
            $this->paymentWarning($user);
        }

        //User status changed to suspended
        if (($original['status'] != MemberStatus::Suspended) && ($user->status == MemberStatus::Suspended)) {
            $this->suspended($user);
        }

        //User status changed to leaving
        if (($original['status'] != MemberStatus::Leaving) && ($user->status == MemberStatus::Leaving)) {
            $this->userLeaving($user);
        }

        //User left
        if (($original['status'] != MemberStatus::Left) && ($user->status == MemberStatus::Left) && $user->isBanned() == false) {
            $this->userLeft($user);
        }

        if ($original['active'] === false && $user->active === true) {
            event(new MemberBecameActive($user));
        }
        if ($original['active'] === true && $user->active === false) {
            event(new MemberBecameInactive($user));
        }

        // Make sure this list is in sync with the fields DiscourseSyncSubscriber uses
        // TODO: Flip dependency here to pull these from one centralised list on user? Or some intermediary class?
        $discourseFields = [
            'email',
            'display_name',
            'suppress_real_name',
            'given_name',
            'family_name',
            'banned',
        ];        
        if (!empty(array_intersect($discourseFields, array_keys($user->getDirty())))) {
            event(new MemberDiscourseParamsChanged($user));
        };
    }

    /**
     * Method called when a user is activated
     * @param $user
     */
    private function newUser($user)
    {
        $userMailer = new UserMailer($user);
        $userMailer->sendWelcomeMessage();

        $telegramHelper = new TelegramHelper("UserObserver");
        $message = "New Member! Welcome to " . $user->name;
        Log::info($message);
        $telegramHelper->notify(
            TelegramHelper::RENDER, 
            $message
        );

    }

    private function sendConfirmationEmail($user){
        $userMailer = new UserMailer($user);
        $userMailer->sendConfirmationEmail();
    }

    private function paymentWarning($user)
    {
        $userMailer = new UserMailer($user);
        $userMailer->sendPaymentWarningMessage();

        $telegramHelper = new TelegramHelper("UserObserver");
        $message = "User marked with payment warning: " . $user->name;
        Log::info($message);
        $telegramHelper->notify(
            TelegramHelper::RENDER, 
            $message
        );

    }

    private function suspended($user)
    {
        $userMailer = new UserMailer($user);
        $userMailer->sendSuspendedMessage();

        $telegramHelper = new TelegramHelper("UserObserver");
        $message = "User marked as suspended for non payment: " . $user->name;
        Log::info($message);
        $telegramHelper->notify(
            TelegramHelper::RENDER, 
            $message
        );
    }

    private function userLeaving($user)
    {
        $userMailer = new UserMailer($user);
        $userMailer->sendLeavingMessage();

        $telegramHelper = new TelegramHelper("UserObserver");
        $message = "User marked as leaving: " . $user->name;
        Log::info($message);
        $telegramHelper->notify(
            TelegramHelper::RENDER, 
            $message
        );
    }

    private function userLeft($user)
    {
        $userMailer = new UserMailer($user);
        $userMailer->sendLeftMessage();

        $telegramHelper = new TelegramHelper("UserObserver");
        $message = "User marked as left: " . $user->name;
        Log::info($message);
        $telegramHelper->notify(
            TelegramHelper::RENDER, 
            $message
        );
    }
} 