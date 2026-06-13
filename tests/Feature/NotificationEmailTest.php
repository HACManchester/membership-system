<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\Induction;
use BB\Entities\User;
use BB\Exceptions\NotImplementedException;
use BB\Mail\EquipmentNotificationEmail;
use BB\Mail\NotificationEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function equipmentWithTrainerAndTrainedUser()
    {
        $equipment = factory(Equipment::class)->create([
            'induction_category' => 'test-tool',
            'requires_induction' => true,
        ]);

        $trainer = factory(User::class)->create(['status' => 'active', 'active' => true]);
        (new Induction([
            'key' => 'test-tool',
            'user_id' => $trainer->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => true,
            'trainer_user_id' => $trainer->id,
        ]))->save();

        $trainedUser = factory(User::class)->create(['status' => 'active', 'active' => true]);
        (new Induction([
            'key' => 'test-tool',
            'user_id' => $trainedUser->id,
            'trained' => now(),
            'active' => true,
            'is_trainer' => false,
            'trainer_user_id' => $trainer->id,
        ]))->save();

        return [$equipment, $trainer, $trainedUser];
    }

    /** @test */
    public function an_admin_can_send_a_notification_to_all_active_members()
    {
        $admin = factory(User::class)->state('admin')->create(['status' => 'active', 'active' => true]);
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);

        $response = $this->actingAs($admin)->post(route('notificationemail.store'), [
            'subject' => 'Open evening',
            'message' => "First line\nSecond line",
            'recipient' => 'all',
            'send_to_all' => '1',
        ]);

        $response->assertRedirect(route('home'));
        Mail::assertQueued(NotificationEmail::class, function ($mail) use ($member) {
            return $mail->hasTo($member->email);
        });
    }

    /** @test */
    public function the_message_body_is_escaped_before_being_marked_up()
    {
        $admin = factory(User::class)->state('admin')->create(['status' => 'active', 'active' => true]);

        $this->actingAs($admin)->post(route('notificationemail.store'), [
            'subject' => 'Test',
            'message' => "<script>alert(1)</script>\nsecond line",
            'recipient' => 'all',
            'send_to_all' => '1',
        ]);

        Mail::assertQueued(NotificationEmail::class, function ($mail) {
            $mail->build();
            $body = $mail->messageBody;

            return strpos($body, '<script>') === false
                && strpos($body, '&lt;script&gt;') !== false
                && strpos($body, '<br />') !== false;
        });
    }

    /** @test */
    public function a_regular_member_cannot_send_to_all_members()
    {
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);

        $response = $this->actingAs($member)->post(route('notificationemail.store'), [
            'subject' => 'Spam',
            'message' => 'Spam',
            'recipient' => 'all',
            'send_to_all' => '1',
        ]);

        $response->assertStatus(403);
        Mail::assertNotQueued(NotificationEmail::class);
    }

    /** @test */
    public function a_trainer_can_email_members_trained_on_their_tool()
    {
        [$equipment, $trainer, $trainedUser] = $this->equipmentWithTrainerAndTrainedUser();

        $response = $this->actingAs($trainer)->post(route('notificationemail.store'), [
            'subject' => 'Tool maintenance',
            'message' => 'The tool is down this week.',
            'recipient' => "tool/{$equipment->slug}/trained",
            'send_to_all' => '1',
        ]);

        $response->assertRedirect(route('equipment.show', $equipment->slug));
        Mail::assertQueued(EquipmentNotificationEmail::class, function ($mail) use ($trainedUser) {
            return $mail->hasTo($trainedUser->email);
        });
    }

    /** @test */
    public function a_non_trainer_cannot_email_a_tool_group()
    {
        // Regression test: this path previously crashed with an undefined
        // $users variable instead of denying access.
        [$equipment] = $this->equipmentWithTrainerAndTrainedUser();
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);

        $response = $this->actingAs($member)->post(route('notificationemail.store'), [
            'subject' => 'Not my tool',
            'message' => 'Hello',
            'recipient' => "tool/{$equipment->slug}/trained",
            'send_to_all' => '1',
        ]);

        $response->assertStatus(403);
        Mail::assertNotQueued(EquipmentNotificationEmail::class);
        Mail::assertNotQueued(NotificationEmail::class);
    }

    /** @test */
    public function an_unrecognised_recipient_group_is_rejected()
    {
        // Regression test: this path previously crashed with an undefined
        // $users variable instead of rejecting the recipient.
        $admin = factory(User::class)->state('admin')->create(['status' => 'active', 'active' => true]);

        $this->withoutExceptionHandling();
        $this->expectException(NotImplementedException::class);

        $this->actingAs($admin)->post(route('notificationemail.store'), [
            'subject' => 'Test',
            'message' => 'Test',
            'recipient' => 'not-a-real-group',
            'send_to_all' => '1',
        ]);
    }

    /** @test */
    public function a_member_can_send_a_test_email_to_themselves()
    {
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);

        $response = $this->actingAs($member)->post(route('notificationemail.store'), [
            'subject' => 'Preview',
            'message' => 'Just checking the layout.',
            'recipient' => 'all',
            'send_to_all' => '0',
        ]);

        $response->assertRedirect(route('home'));
        Mail::assertQueued(NotificationEmail::class, function ($mail) use ($member) {
            return $mail->hasTo($member->email);
        });
    }
}
