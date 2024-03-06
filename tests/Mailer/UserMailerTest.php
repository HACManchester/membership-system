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

        \Mail::assertSent(WelcomeMember::class, function ($mail) use ($user) {
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

        \Mail::assertSent(WelcomeMemberOnlineOnly::class, function ($mail) use ($user) {
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

        \Mail::assertSent(ConfirmationEmail::class, function ($mail) use ($user) {
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

        \Mail::assertSent(PaymentWarning::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'We have detected a payment problem';
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

        \Mail::assertSent(LeavingMessage::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'You are leaving Hackspace Manchester';
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

        \Mail::assertSent(LeftMessage::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->subject == 'You have left Hackspace Manchester';
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

        \Mail::assertSent(NotificationEmail::class, function ($mail) use ($user) {
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

        \Mail::assertSent(EquipmentNotificationEmail::class, function ($mail) use ($user) {
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

        \Mail::assertSent(SuspendedMessage::class, function ($mail) use ($user) {
            $mail->build();
            return $mail->hasTo($user->email, $user->display_name) &&
                $mail->replyTo[0]['address'] == 'board@hacman.org.uk' &&
                $mail->replyTo[0]['name'] == 'Hackspace Manchester Board' &&
                $mail->subject == 'Your Hackspace Manchester membership has been suspended';
        });
    }
}
