<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use BB\Events\MemberGivenTrustedStatus;
use BB\Events\MemberPhotoWasDeclined;
use BB\Notifications\Messages\MailMessage;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Covers the mail paths the route smoke test can't: it already renders every
 * Mailable and notification through the preview pages, but only asserts they
 * don't 500.
 *
 * Laravel 9 replaces SwiftMailer with Symfony Mailer, and these are the two
 * places most exposed to that:
 *
 *  - Our MailMessage subclass overrides the framework's with()/action() so a
 *    notification can carry several buttons (the framework supports one). If
 *    the parent class changes underneath it, buttons silently vanish.
 *  - Two listeners send raw views via the closure-based Mailer::send() API
 *    rather than a Mailable.
 *
 * Sent messages are read through sentMessage*() helpers that understand both
 * Swift_Message and Symfony's Email, so the same assertions hold either side
 * of the swap — which is the point.
 */
class MailRenderTest extends TestCase
{
    #[Test]
    public function every_action_on_a_notification_renders_as_its_own_button()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('notification-preview.induction-completed'));

        $response->assertStatus(200);
        $response->assertSee('Visit our forum');
        $response->assertSee('https://list.hacman.org.uk', false);
        $response->assertSee('Join us on Telegram');
        $response->assertSee('https://docs.hacman.org.uk/getting_started/communications/telegram/', false);
    }

    /**
     * Pins the subclass's contract directly, so a change in the parent
     * MailMessage fails here with a clear reason rather than as a missing
     * button in an email nobody checks.
     */
    #[Test]
    public function actions_are_collected_separately_from_lines_and_bypass_the_single_action_slot()
    {
        $message = (new MailMessage)
            ->line('First line')
            ->action('Button one', 'https://example.test/one')
            ->line('Second line')
            ->action('Button two', 'https://example.test/two');

        $this->assertCount(2, $message->actionLines);
        $this->assertSame('Button one', $message->actionLines[0]->text);
        $this->assertSame('https://example.test/two', $message->actionLines[1]->url);

        $this->assertNull($message->actionText, 'the framework single-action slot should stay unused');

        $data = $message->toArray();
        $this->assertSame($message->actionLines, $data['actionLines']);
        $this->assertSame(['First line', 'Second line'], array_merge($data['introLines'], $data['outroLines']));
    }

    #[Test]
    public function a_member_made_trusted_is_emailed()
    {
        $user = $this->member('Ada', 'ada@example.test');
        $sent = $this->captureSentMail();

        event(new MemberGivenTrustedStatus($user));

        $this->assertCount(1, $sent);
        $this->assertSame(['ada@example.test'], $this->sentMessageRecipients($sent[0]));
        $this->assertSame('You have been made a trusted member', $this->sentMessageSubject($sent[0]));
        $this->assertStringContainsString('Hi Ada', $this->sentMessageBody($sent[0]));
        $this->assertStringContainsString('You have been made a trusted member', $this->sentMessageBody($sent[0]));
    }

    #[Test]
    public function a_member_whose_photo_is_declined_is_emailed()
    {
        $user = $this->member('Grace', 'grace@example.test');
        $sent = $this->captureSentMail();

        event(new MemberPhotoWasDeclined($user));

        $this->assertCount(1, $sent);
        $this->assertSame(['grace@example.test'], $this->sentMessageRecipients($sent[0]));
        $this->assertSame('Your profile photo was rejected', $this->sentMessageSubject($sent[0]));
        $this->assertStringContainsString('Hi Grace', $this->sentMessageBody($sent[0]));
        $this->assertStringContainsString('profile photo has been rejected', $this->sentMessageBody($sent[0]));
    }

    private function member($givenName, $email)
    {
        $user = User::factory()->create(['given_name' => $givenName, 'email' => $email]);
        ProfileData::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Collects every message the real mailer sends. Mail::fake() can't be used:
     * it only records Mailables and silently drops raw view sends.
     *
     * @return \ArrayObject<int,object>
     */
    private function captureSentMail()
    {
        $sent = new \ArrayObject();

        Event::listen(MessageSent::class, function (MessageSent $event) use ($sent) {
            $sent[] = $event->message;
        });

        return $sent;
    }

    /**
     * @param \Swift_Message|\Symfony\Component\Mime\Email $message
     * @return string[]
     */
    private function sentMessageRecipients($message)
    {
        $to = $message->getTo();

        // Swift: ['email' => 'name']; Symfony: Address[]
        if (is_array($to) && ! isset($to[0])) {
            return array_keys($to);
        }

        return array_map(function ($address) {
            return $address->getAddress();
        }, $to);
    }

    private function sentMessageSubject($message)
    {
        return $message->getSubject();
    }

    private function sentMessageBody($message)
    {
        return method_exists($message, 'getHtmlBody') ? $message->getHtmlBody() : $message->getBody();
    }
}
