<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Mail\PaymentWarning;
use BB\Mail\SuspendedMessage;
use BB\Mail\LeavingMessage;
use BB\Mail\LeftMessage;
use BB\Mail\WelcomeMember;
use BB\Mail\NotificationEmail;
use BB\Mail\EquipmentNotificationEmail;
use BB\Mail\ConfirmationEmail;
use BB\Support\MemberStatus;
use Inertia\Inertia;
use Inertia\Response;

class EmailPreviewController extends Controller
{
    public function index(): Response
    {
        $emails = [
            'Payment Warning' => 'email-preview.payment-warning',
            'Suspended' => 'email-preview.suspended',
            'Leaving' => 'email-preview.leaving',
            'Left' => 'email-preview.left',
            'Welcome Member' => 'email-preview.welcome-member',
            'Notification Email' => 'email-preview.notification',
            'Equipment Notification' => 'email-preview.equipment-notification',
            'Confirmation Email' => 'email-preview.confirmation',
        ];

        $notifications = [
            'Induction Completed' => 'notification-preview.induction-completed',
            'Marked as Trainer' => 'notification-preview.induction-marked-as-trainer',
            'Induction Requested (Inductee)' => 'notification-preview.inductee-induction-requested',
            'Induction Requested (Trainer)' => 'notification-preview.trainer-induction-requested',
            'Course: Induction Completed' => 'notification-preview.course-induction-completed',
            'Course: Marked as Trainer' => 'notification-preview.course-induction-marked-as-trainer',
            'Course: Induction Requested (Inductee)' => 'notification-preview.course-inductee-induction-requested',
            'Course: Induction Requested (Trainer)' => 'notification-preview.course-trainer-induction-requested',
        ];

        return Inertia::render('EmailPreview/Index', [
            'emails' => $this->previewLinks($emails),
            'notifications' => $this->previewLinks($notifications),
        ]);
    }

    /**
     * @param  array<string, string>  $routes  route names keyed by label
     * @return list<array{name: string, url: string}>
     */
    private function previewLinks(array $routes): array
    {
        return array_map(
            fn (string $name, string $route) => ['name' => $name, 'url' => route($route, [], false)],
            array_keys($routes),
            $routes,
        );
    }

    public function paymentWarning()
    {
        $user = $this->getDummyUser();
        return new PaymentWarning($user);
    }

    public function suspended()
    {
        $user = User::factory()->make([
            'id' => 999,
            'status' => MemberStatus::Suspended,
            'active' => false,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);

        return new SuspendedMessage($user);
    }

    public function leaving()
    {
        $user = User::factory()->make([
            'id' => 999,
            'status' => MemberStatus::Leaving,
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);

        return new LeavingMessage($user);
    }

    public function left()
    {
        $user = User::factory()->make([
            'id' => 999,
            'status' => MemberStatus::Left,
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
        return User::factory()->make([
            'id' => 999,
            'status' => MemberStatus::Active,
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);
    }
}
