<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingInterestWithdrawnEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourseTrainingWaitlistTest extends TestCase
{
    use RefreshDatabase;

    protected $course;
    protected $trainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::factory()->create(['frequency' => 'regular']);

        $this->trainer = User::factory()->create();
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

    #[Test]
    public function waitlist_is_ordered_longest_waiting_first()
    {
        // Created in shuffled order so insertion order can't mask a sort bug
        $middle = $this->waitlistRecord(User::factory()->create(), 5);
        $newest = $this->waitlistRecord(User::factory()->create(), 1);
        $oldest = $this->waitlistRecord(User::factory()->create(), 10);

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

    #[Test]
    public function waitlist_only_contains_members_awaiting_training()
    {
        $waiting = $this->waitlistRecord(User::factory()->create(), 3);

        // Expired sign-off requests drop back onto the waitlist
        $expiredSignOff = $this->record(User::factory()->create(), [
            'sign_off_requested_at' => Carbon::now()->subDays(8),
        ]);

        // None of these belong on the waitlist
        $this->record(User::factory()->create(), ['trained' => Carbon::now()]);
        $this->record(User::factory()->create(), [
            'sign_off_requested_at' => Carbon::now()->subHour(),
        ]);
        $this->record(User::factory()->create(['active' => false]));

        $response = $this->actingAs($this->trainer)
            ->get(route('courses.training.index', $this->course));

        $response->assertInertia(function ($page) use ($waiting, $expiredSignOff) {
            $page->component('CourseTraining/Index')
                ->has('waitlist', 2)
                ->where('waitlist.0.id', $waiting->id)
                ->where('waitlist.1.id', $expiredSignOff->id);
        });
    }

    #[Test]
    public function trainer_can_remove_a_member_from_the_waitlist()
    {
        Event::fake([TrainingInterestWithdrawnEvent::class]);
        $member = User::factory()->create();
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

    #[Test]
    public function trained_members_cannot_be_removed_via_the_waitlist()
    {
        $member = User::factory()->create();
        $record = $this->record($member, ['trained' => Carbon::now()]);

        $response = $this->actingAs($this->trainer)
            ->delete(route('courses.training.remove-from-waitlist', [
                'course' => $this->course,
                'user' => $member,
            ]));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
    }

    #[Test]
    public function non_trainers_cannot_remove_members_from_the_waitlist()
    {
        $member = User::factory()->create();
        $record = $this->waitlistRecord($member, 3);

        $response = $this->actingAs(User::factory()->create())
            ->delete(route('courses.training.remove-from-waitlist', [
                'course' => $this->course,
                'user' => $member,
            ]));

        $response->assertForbidden();
        $this->assertDatabaseHas('inductions', ['id' => $record->id]);
    }

    #[Test]
    public function admins_can_remove_members_from_the_waitlist()
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
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
