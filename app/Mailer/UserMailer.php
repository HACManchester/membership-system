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
    
            $addressRepository = \App::make('BB\Repo\AddressRepository');
            $address = $addressRepository->getActiveUserAddress($this->user->id);
    
            \Mail::queue('emails.welcome-admin', ['user'=>$user, 'address'=>$address], function ($message) use ($user,$address) {
                $message->to('outreach@hacman.org.uk')->subject('New Member Alert');
            });
        }
    }
    



    public function sendPaymentWarningMessage()
    {
        $user = $this->user;
        \Mail::queue('emails.payment-warning', ['user'=>$user], function ($message) use ($user) {
            $message->to($user->email, $user->email)->subject('We have detected a payment problem');
        });
    }


    public function sendLeftMessage()
    {
        $user = $this->user;

        $storageBoxRepository = \App::make('BB\Repo\StorageBoxRepository');

        $memberBox = $storageBoxRepository->getMemberBox($this->user->id);

        \Mail::queue('emails.user-left', ['user'=>$user, 'memberBox'=>$memberBox], function ($message) use ($user) {
            $message->to($user->email, $user->email)->subject('Sorry to see you go');
        });
    }


    public function sendNotificationEmail($subject, $message)
    {
        $user = $this->user;
        \Mail::queue('emails.notification', ['messageBody'=>$message, 'user'=>$user], function ($message) use ($user, $subject) {
            $message->addReplyTo('board@hacman.org.uk', 'Hackspace Manchester Board');
            $message->to($user->email, $user->email)->subject($subject);
        });
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
