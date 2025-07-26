<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Mail\PaymentWarning;
use BB\Mail\SuspendedMessage;
use BB\Mail\LeavingMessage;
use BB\Mail\LeftMessage;
use BB\Mail\WelcomeMember;
use BB\Mail\WelcomeMemberOnlineOnly;
use BB\Mail\NotificationEmail;
use BB\Mail\EquipmentNotificationEmail;
use BB\Mail\ConfirmationEmail;

class EmailPreviewController extends Controller
{
    public function index()
    {
        $emails = [
            'Payment Warning' => route('email-preview.payment-warning'),
            'Suspended' => route('email-preview.suspended'),
            'Leaving' => route('email-preview.leaving'),
            'Left' => route('email-preview.left'),
            'Welcome Member' => route('email-preview.welcome-member'),
            'Welcome Member (Online Only)' => route('email-preview.welcome-member-online-only'),
            'Notification Email' => route('email-preview.notification'),
            'Equipment Notification' => route('email-preview.equipment-notification'),
            'Confirmation Email' => route('email-preview.confirmation'),
        ];

        $notifications = [
            'Induction Completed' => route('notification-preview.induction-completed'),
            'Marked as Trainer' => route('notification-preview.induction-marked-as-trainer'),
            'Induction Requested (Inductee)' => route('notification-preview.inductee-induction-requested'),
            'Induction Requested (Trainer)' => route('notification-preview.trainer-induction-requested'),
            'Course: Induction Completed' => route('notification-preview.course-induction-completed'),
            'Course: Marked as Trainer' => route('notification-preview.course-induction-marked-as-trainer'),
            'Course: Induction Requested (Inductee)' => route('notification-preview.course-inductee-induction-requested'),
            'Course: Induction Requested (Trainer)' => route('notification-preview.course-trainer-induction-requested'),
        ];

        return view('email-preview.index', compact('emails', 'notifications'));
    }
    public function paymentWarning()
    {
        $user = $this->getDummyUser();
        return new PaymentWarning($user);
    }

    public function suspended()
    {
        $user = factory(User::class)->make([
            'id' => 999,
            'status' => 'suspended',
            'active' => false,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);

        return new SuspendedMessage($user);
    }

    public function leaving()
    {
        $user = factory(User::class)->make([
            'id' => 999,
            'status' => 'leaving',
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);

        return new LeavingMessage($user);
    }

    public function left()
    {
        $user = factory(User::class)->make([
            'id' => 999,
            'status' => 'left',
            'active' => false,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);
        return new LeftMessage($user);
    }

    public function welcomeMember()
    {
        $user = $this->getDummyUser();
        return new WelcomeMember($user);
    }

    public function welcomeMemberOnlineOnly()
    {
        $user = $this->getDummyUser();
        return new WelcomeMemberOnlineOnly($user);
    }

    public function notification()
    {
        $user = $this->getDummyUser();
        $subjectLine = 'Important Hackspace Update';
        $messageBody = 'This is a sample notification message to all members about an important update regarding the hackspace operations.';
        return new NotificationEmail($user, $subjectLine, $messageBody);
    }

    public function equipmentNotification()
    {
        $user = $this->getDummyUser();
        $subjectLine = 'Equipment Training Update';
        $messageBody = 'Your training status for the equipment has been updated. Please check your account for more details.';
        $equipmentName = '3D Printer';
        $trainingStatus = 'Trained';
        return new EquipmentNotificationEmail($user, $subjectLine, $messageBody, $equipmentName, $trainingStatus);
    }

    public function confirmation()
    {
        $user = $this->getDummyUser();
        return new ConfirmationEmail($user);
    }

    private function getDummyUser()
    {
        return factory(User::class)->make([
            'id' => 999,
            'status' => 'active',
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);
    }
}
