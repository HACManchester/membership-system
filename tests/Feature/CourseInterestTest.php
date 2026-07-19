<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingInterestRegisteredEvent;
use BB\Events\TrainingRecords\TrainingInterestWithdrawnEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CourseInterestTest extends TestCase
{
    use RefreshDatabase;

    protected $course;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = factory(Course::class)->create(['frequency' => 'regular']);
        $this->user = factory(User::class)->create();

        Event::fake([
            TrainingInterestRegisteredEvent::class,
            TrainingInterestWithdrawnEvent::class,
        ]);
    }

    private function waitlistRecord(User $user, array $attributes = []): TrainingRecord
    {
        $record = new TrainingRecord(array_merge([
            'user_id' => $user->id,
            'key' => $this->course->slug,
            'course_id' => $this->course->id,
        ], $attributes));
        $record->save();

        return $record;
    }

    /** @test */
    public function member_can_join_the_waitlist()
    {
        $response = $this->actingAs($this->user)
            ->post(route('courses.interest.store', $this->course));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('inductions', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'key' => $this->course->slug,
            'trained' => null,
            'sign_off_requested_at' => null,
        ]);
        Event::assertDispatched(TrainingInterestRegisteredEvent::class, function ($event) {
            return $event->trainingRecord->user_id === $this->user->id;
        });
    }

    /** @test */
    public function joining_twice_keeps_one_record_and_fires_one_event()
    {
        $this->actingAs($this->user)->post(route('courses.interest.store', $this->course));
        $this->actingAs($this->user)->post(route('courses.interest.store', $this->course));

        $this->assertEquals(1, TrainingRecord::where('user_id', $this->user->id)
            ->where('course_id', $this->course->id)->count());
        Event::assertDispatchedTimes(TrainingInterestRegisteredEvent::class, 1);
    }

    /** @test */
    public function member_can_join_while_the_course_is_paused()
    {
        $this->course->update(['paused_at' => Carbon::now()]);

        $response = $this->actingAs($this->user)
            ->post(route('courses.interest.store', $this->course));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('inductions', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);
    }

    /** @test */
    public function member_cannot_join_the_waitlist_of_a_self_serve_course()
    {
        $selfServe = factory(Course::class)->create(['frequency' => 'self-serve']);

        $response = $this->actingAs($this->user)
            ->post(route('courses.interest.store', $selfServe));

        $response->assertForbidden();
        $this->assertDatabaseMissing('inductions', [
            'user_id' => $this->user->id,
            'course_id' => $selfServe->id,
        ]);
    }

    /** @test */
    public function trained_member_cannot_join_the_waitlist()
    {
        $trainedAt = Carbon::now()->subDay();
        $this->waitlistRecord($this->user, ['trained' => $trainedAt]);

        $response = $this->actingAs($this->user)
            ->post(route('courses.interest.store', $this->course));

        $response->assertSessionHas('error');
        $this->assertEquals(1, TrainingRecord::where('user_id', $this->user->id)
            ->where('course_id', $this->course->id)->whereNotNull('trained')->count());
        Event::assertNotDispatched(TrainingInterestRegisteredEvent::class);
    }

    /** @test */
    public function joining_with_an_existing_expired_sign_off_record_is_idempotent()
    {
        $requestedAt = Carbon::now()->subDays(8);
        $this->waitlistRecord($this->user, ['sign_off_requested_at' => $requestedAt]);

        $response = $this->actingAs($this->user)
            ->post(route('courses.interest.store', $this->course));

        $response->assertSessionHas('success');
        $this->assertEquals(1, TrainingRecord::where('user_id', $this->user->id)
            ->where('course_id', $this->course->id)->count());
        Event::assertNotDispatched(TrainingInterestRegisteredEvent::class);
    }

    /** @test */
    public function member_can_withdraw_from_the_waitlist()
    {
        $record = $this->waitlistRecord($this->user);

        $response = $this->actingAs($this->user)
            ->delete(route('courses.interest.destroy', $this->course));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('inductions', ['id' => $record->id]);
        Event::assertDispatched(TrainingInterestWithdrawnEvent::class, function ($event) {
            return $event->user->id === $this->user->id
                && $event->course->id === $this->course->id;
        });
    }

    /** @test */
    public function withdrawal_is_blocked_when_not_on_the_waitlist()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('courses.interest.destroy', $this->course));

        $response->assertSessionHas('error');
        Event::assertNotDispatched(TrainingInterestWithdrawnEvent::class);
    }

    /** @test */
    public function withdrawal_is_blocked_for_trained_members()
    {
        $record = $this->waitlistRecord($this->user, ['trained' => Carbon::now()]);

        $response = $this->actingAs($this->user)
            ->delete(route('courses.interest.destroy', $this->course));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
        Event::assertNotDispatched(TrainingInterestWithdrawnEvent::class);
    }

    /** @test */
    public function withdrawal_is_blocked_for_trainers()
    {
        $record = $this->waitlistRecord($this->user, ['is_trainer' => true]);

        $response = $this->actingAs($this->user)
            ->delete(route('courses.interest.destroy', $this->course));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
    }

    /** @test */
    public function withdrawal_is_blocked_while_a_sign_off_request_is_pending()
    {
        $record = $this->waitlistRecord($this->user, [
            'sign_off_requested_at' => Carbon::now()->subHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('courses.interest.destroy', $this->course));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
    }

    /** @test */
    public function withdrawal_is_allowed_once_a_sign_off_request_has_expired()
    {
        $record = $this->waitlistRecord($this->user, [
            'sign_off_requested_at' => Carbon::now()->subDays(8),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('courses.interest.destroy', $this->course));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('inductions', ['id' => $record->id]);
    }

    /** @test */
    public function guests_cannot_join_the_waitlist()
    {
        $response = $this->post(route('courses.interest.store', $this->course));

        $response->assertRedirect();
        $this->assertEquals(0, TrainingRecord::where('course_id', $this->course->id)->count());
    }
}
