<?php namespace BB\Observer;

use BB\Mailer\UserMailer;
use BB\Helpers\TelegramHelper;

class UserObserver
{
    /**
     * Welcome online only users when they create their account
     */
    public function created($user){
        
        if($user->online_only) {
            $this->newUser($user);
        }
    }

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
        if (($original['status'] == 'setting-up') && ($user->status != 'setting-up')) {
            $this->newUser($user);
        }

        //User status changed to payment warning
        if (($original['status'] != 'payment-warning') && ($user->status == 'payment-warning')) {
            $this->paymentWarning($user);
        }

        //User status changed to payment warning
        if (($original['status'] != 'suspended') && ($user->status == 'suspended')) {
            $this->suspended($user);
        }

        //User left
        if (($original['status'] != 'left') && ($user->status == 'left')) {
            $this->userLeft($user);
        }
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
        \Log::info($message);
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
        \Log::info($message);
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
        \Log::info($message);
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
        \Log::info($message);
        $telegramHelper->notify(
            TelegramHelper::RENDER, 
            $message
        );
    }
} 