<?php

namespace BB\Mailer;

use BB\Entities\User;
use BB\Mail\ConfirmationEmail;
use BB\Mail\EquipmentNotificationEmail;
use BB\Mail\LeavingMessage;
use BB\Mail\LeftMessage;
use BB\Mail\NotificationEmail;
use BB\Mail\PaymentWarning;
use BB\Mail\SuspendedMessage;
use BB\Mail\WelcomeMember;
use BB\Mail\WelcomeMemberOnlineOnly;
use Mail;

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

        if ($user->online_only) {
            Mail::to($user)->send(new WelcomeMemberOnlineOnly($user));
        } else {
            Mail::to($user)->send(new WelcomeMember($user));
        }
    }

    public function sendConfirmationEmail()
    {
        $user = $this->user;
        Mail::to($user)->send(new ConfirmationEmail($user));
    }


    public function sendPaymentWarningMessage()
    {
        $user = $this->user;
        Mail::to($user)->send(new PaymentWarning($user));
    }


    public function sendLeavingMessage()
    {
        $user = $this->user;
        $storageBoxRepository = \App::make('BB\Repo\StorageBoxRepository');
        $memberBox = $storageBoxRepository->getMemberBox($this->user->id);

        Mail::to($user)->send(new LeavingMessage($user, $memberBox));
    }


    public function sendLeftMessage()
    {
        $user = $this->user;
        $storageBoxRepository = \App::make('BB\Repo\StorageBoxRepository');
        $memberBox = $storageBoxRepository->getMemberBox($this->user->id);

        Mail::to($user)->send(new LeftMessage($user, $memberBox));
    }


    public function sendNotificationEmail($subject, $message)
    {
        //TODO, check if sent by trainer and apply template with correct signature
        $user = $this->user;
        Mail::to($user)->send(new NotificationEmail($user, $subject, $message));
    }

    public function sendEquipmentNotificationEmail($subject, $message, $equipment_name, $training_status)
    {
        //TODO, check if sent by trainer and apply template with correct signature
        $user = $this->user;
        Mail::to($user)->send(new EquipmentNotificationEmail($user, $subject, $message, $equipment_name, $training_status));
    }

    public function sendSuspendedMessage()
    {
        $user = $this->user;
        Mail::to($user)->send(new SuspendedMessage($user));
    }
}
