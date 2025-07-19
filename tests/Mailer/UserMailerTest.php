<?php

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
use BB\Mailer\UserMailer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserMailerTest extends TestCase
{
    use DatabaseMigrations;

    public function testSendWelcomeMessage()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
            'display_name' => 'John Doe',
            'online_only' => false,
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendWelcomeMessage();

        \Mail::assertQueued(WelcomeMember::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'Welcome to Hackspace Manchester!';
        });
    }

    public function testSendWelcomeMessage_OnlineOnly()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
            'display_name' => 'John Doe',
            'online_only' => true,
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendWelcomeMessage();

        \Mail::assertQueued(WelcomeMemberOnlineOnly::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'Welcome to Hackspace Manchester (Online Only)!';
        });
    }

    public function testSendConfirmationEmail()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
            'display_name' => 'John Doe',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendConfirmationEmail();

        \Mail::assertQueued(ConfirmationEmail::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'Confirm your email - Hackspace Manchester';
        });
    }

    public function testSendPaymentWarningMessage()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendPaymentWarningMessage();

        \Mail::assertQueued(PaymentWarning::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'Payment issue - Action required for your Hackspace Membership';
        });
    }

    public function testSendLeavingMessage()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendLeavingMessage();

        \Mail::assertQueued(LeavingMessage::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'Membership cancellation confirmed - you\'re leaving Hackspace Manchester';
        });
    }

    public function testSendLeftMessage()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendLeftMessage();

        \Mail::assertQueued(LeftMessage::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'Your Hackspace Manchester membership has ended';
        });
    }

    public function testSendNotificationEmail()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendNotificationEmail('Test Subject', 'Test Message');

        \Mail::assertQueued(NotificationEmail::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->replyTo[0]['address'] == 'board@hacman.org.uk' &&
                $mail->replyTo[0]['name'] == 'Hackspace Manchester Board' &&
                $mail->subject == 'Test Subject';
        });
    }

    public function testSendEquipmentNotificationEmail()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendEquipmentNotificationEmail('Test Subject', 'Test Message', 'Equipment Name', 'Training Status');

        \Mail::assertQueued(EquipmentNotificationEmail::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->replyTo[0]['address'] == 'info@hacman.org.uk' &&
                $mail->replyTo[0]['name'] == 'Hackspace Manchester Info' &&
                $mail->subject == 'Test Subject';
        });
    }

    public function testSendSuspendedMessage()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'test@example.com',
        ]);

        $mailer = new UserMailer($user);
        $mailer->sendSuspendedMessage();

        \Mail::assertQueued(SuspendedMessage::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->replyTo[0]['address'] == 'board@hacman.org.uk' &&
                $mail->replyTo[0]['name'] == 'Hackspace Manchester Board' &&
                $mail->subject == 'Membership suspended - Immediate action required';
        });
    }
}
