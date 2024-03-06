<?php namespace BB\Mailer;

use BB\Entities\User;

class UserMailer
{


    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Send a welcome email
     */
    public function sendWelcomeMessage()
    {
        $user = $this->user;

        if ($user->online_only){
            \Mail::queue('emails.welcome-online-only', ['user'=>$user], function ($message) use ($user) {
                $message->to($user->email, $user->name)->subject('Welcome to Hackspace Manchester (Online Only)!');
            });
        } else {
            \Mail::queue('emails.welcome', ['user'=>$user], function ($message) use ($user) {
                $message->to($user->email, $user->name)->subject('Welcome to Hackspace Manchester!');
            });
        }
    }
    
    public function sendConfirmationEmail(){
        $user = $this->user;
        
        \Mail::queue('emails.confirm-email', ['user'=>$user], function ($message) use ($user) {
            $message->to($user->email, $user->name)->subject('Confirm your email - Hackspace Manchester');
        });
    }


    public function sendPaymentWarningMessage()
    {
        $user = $this->user;
        \Mail::queue('emails.payment-warning', ['user'=>$user], function ($message) use ($user) {
            $message->to($user->email, $user->email)->subject('We have detected a payment problem');
        });
    }


    public function sendLeavingMessage()
    {
        $user = $this->user;

        $storageBoxRepository = \App::make('BB\Repo\StorageBoxRepository');

        $memberBox = $storageBoxRepository->getMemberBox($this->user->id);

        \Mail::queue('emails.user-leaving', ['user'=>$user, 'memberBox'=>$memberBox], function ($message) use ($user) {
            $message->to($user->email, $user->email)->subject('You are leaving Hackspace Manchester');
        });
    }


    public function sendLeftMessage()
    {
        $user = $this->user;

        $storageBoxRepository = \App::make('BB\Repo\StorageBoxRepository');

        $memberBox = $storageBoxRepository->getMemberBox($this->user->id);

        \Mail::queue('emails.user-left', ['user'=>$user, 'memberBox'=>$memberBox], function ($message) use ($user) {
            $message->to($user->email, $user->email)->subject('You have left Hackspace Manchester');
        });
    }


    public function sendNotificationEmail($subject, $message)
    {
        //TODO, check if sent by trainer and apply template with correct signature
        $user = $this->user;
        \Mail::queue('emails.notification', ['messageBody'=>$message, 'user'=>$user], function ($message) use ($user, $subject) {
            $message->addReplyTo('board@hacman.org.uk', 'Hackspace Manchester Board');
            $message->to($user->email, $user->email)->subject($subject);
        });
    }
 
    public function sendEquipmentNotificationEmail($subject, $message, $equipment_name, $training_status)
    {
        //TODO, check if sent by trainer and apply template with correct signature
        $user = $this->user;
        \Mail::queue(
            'emails.equipment-notification', 
            [
                'messageBody'=>$message, 
                'user'=>$user, 
                'equipment_name'=>$equipment_name, 
                'training_status'=>$training_status
            ], 
            function ($message) use ($user, $subject) {
            $message->addReplyTo('info@hacman.org.uk', 'Hackspace Manchester Info');
            $message->to($user->email, $user->email)->subject($subject);
            }
        );
    }

    public function sendSuspendedMessage()
    {
        $user = $this->user;
        \Mail::queue('emails.suspended', ['user'=>$user], function ($message) use ($user) {
            $message->addReplyTo('board@hacman.org.uk', 'Hackspace Manchester Board');
            $message->to($user->email, $user->email)->subject('Your Hackspace Manchester membership has been suspended');
        });
    }

} 
