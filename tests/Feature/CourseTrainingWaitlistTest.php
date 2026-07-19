<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingInterestWithdrawnEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CourseTrainingWaitlistTest extends TestCase
{
    use RefreshDatabase;

    protected $course;
    protected $trainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = factory(Course::class)->create(['frequency' => 'regular']);

        $this->trainer = factory(User::class)->create();
        $this->record($this->trainer, [
            'trained' => Carbon::now()->subYear(),
            'is_trainer' => true,
        ]);
    }

    private function record(User $user, array $attributes = []): TrainingRecord
    {
        $record = new TrainingRecord(array_merge([
            'user_id' => $user->id,
            'key' => $this->course->slug,
            'course_id' => $this->course->id,
        ], $attributes));
        $record->save();

        return $record;
    }

    private function waitlistRecord(User $user, int $daysWaiting): TrainingRecord
    {
        $record = $this->record($user);
        $record->created_at = Carbon::now()->subDays($daysWaiting);
        $record->save();

        return $record;
    }

    /** @test */
    public function waitlist_is_ordered_longest_waiting_first()
    {
        // Created in shuffled order so insertion order can't mask a sort bug
        $middle = $this->waitlistRecord(factory(User::class)->create(), 5);
        $newest = $this->waitlistRecord(factory(User::class)->create(), 1);
        $oldest = $this->waitlistRecord(factory(User::class)->create(), 10);

        $response = $this->actingAs($this->trainer)
            ->get(route('courses.training.index', $this->course));

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($oldest, $middle, $newest) {
            $page->component('CourseTraining/Index')
                ->has('waitlist', 3)
                ->where('waitlist.0.id', $oldest->id)
                ->where('waitlist.1.id', $middle->id)
                ->where('waitlist.2.id', $newest->id);
        });
    }

    /** @test */
    public function waitlist_only_contains_members_awaiting_training()
    {
        $waiting = $this->waitlistRecord(factory(User::class)->create(), 3);

        // Expired sign-off requests drop back onto the waitlist
        $expiredSignOff = $this->record(factory(User::class)->create(), [
            'sign_off_requested_at' => Carbon::now()->subDays(8),
        ]);

        // None of these belong on the waitlist
        $this->record(factory(User::class)->create(), ['trained' => Carbon::now()]);
        $this->record(factory(User::class)->create(), [
            'sign_off_requested_at' => Carbon::now()->subHour(),
        ]);
        $this->record(factory(User::class)->create(['active' => false]));

        $response = $this->actingAs($this->trainer)
            ->get(route('courses.training.index', $this->course));

        $response->assertInertia(function ($page) use ($waiting, $expiredSignOff) {
            $page->component('CourseTraining/Index')
                ->has('waitlist', 2)
                ->where('waitlist.0.id', $waiting->id)
                ->where('waitlist.1.id', $expiredSignOff->id);
        });
    }

    /** @test */
    public function trainer_can_remove_a_member_from_the_waitlist()
    {
        Event::fake([TrainingInterestWithdrawnEvent::class]);
        $member = factory(User::class)->create();
        $record = $this->waitlistRecord($member, 3);

        $response = $this->actingAs($this->trainer)
            ->delete(route('courses.training.remove-from-waitlist', [
                'course' => $this->course,
                'user' => $member,
            ]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('inductions', ['id' => $record->id]);
        Event::assertDispatched(TrainingInterestWithdrawnEvent::class, function ($event) use ($member) {
            return $event->user->id === $member->id
                && $event->course->id === $this->course->id;
        });
    }

    /** @test */
    public function trained_members_cannot_be_removed_via_the_waitlist()
    {
        $member = factory(User::class)->create();
        $record = $this->record($member, ['trained' => Carbon::now()]);

        $response = $this->actingAs($this->trainer)
            ->delete(route('courses.training.remove-from-waitlist', [
                'course' => $this->course,
                'user' => $member,
            ]));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
    }

    /** @test */
    public function non_trainers_cannot_remove_members_from_the_waitlist()
    {
        $member = factory(User::class)->create();
        $record = $this->waitlistRecord($member, 3);

        $response = $this->actingAs(factory(User::class)->create())
            ->delete(route('courses.training.remove-from-waitlist', [
                'course' => $this->course,
                'user' => $member,
            ]));

        $response->assertForbidden();
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
    }

    /** @test */
    public function admins_can_remove_members_from_the_waitlist()
    {
        $admin = factory(User::class)->state('admin')->create();
        $member = factory(User::class)->create();
        $record = $this->waitlistRecord($member, 3);

        $response = $this->actingAs($admin)
            ->delete(route('courses.training.remove-from-waitlist', [
                'course' => $this->course,
                'user' => $member,
            ]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('inductions', ['id' => $record->id]);
    }
}
