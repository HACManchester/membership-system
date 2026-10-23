<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourseTrainingTrainTest extends TestCase
{
    /** @var User */
    private $admin;

    /** @var User */
    private $member;

    /** @var Course */
    private $course;

    /** @var TrainingRecord */
    private $record;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->admin = factory(User::class)->state('admin')->create();
        $this->member = factory(User::class)->create(['status' => 'active', 'active' => true]);
        $this->course = factory(Course::class)->create();
        $this->record = new TrainingRecord([
            'user_id' => $this->member->id,
            'key' => $this->course->slug,
            'course_id' => $this->course->id,
        ]);
        $this->record->save();
    }

    #[Test]
    public function the_signed_in_trainer_is_recorded_by_default()
    {
        $this->actingAs($this->admin)
            ->post(route('courses.training.train', [$this->course, $this->member]))
            ->assertRedirect(route('courses.training.index', $this->course));

        $this->record->refresh();
        $this->assertNotNull($this->record->trained);
        $this->assertEquals($this->admin->id, $this->record->trainer_user_id);
        Event::assertDispatched(TrainingRecordCompletedEvent::class);
    }

    #[Test]
    public function an_array_of_trainer_ids_is_rejected()
    {
        $this->actingAs($this->admin)
            ->post(route('courses.training.train', [$this->course, $this->member]), [
                'trainer_user_id' => [$this->admin->id],
            ])
            ->assertSessionHasErrors('trainer_user_id');

        $this->assertNull($this->record->refresh()->trained);
    }

    #[Test]
    public function a_member_who_is_not_a_trainer_cannot_train()
    {
        $this->actingAs(factory(User::class)->create(['status' => 'active', 'active' => true]))
            ->post(route('courses.training.train', [$this->course, $this->member]))
            ->assertForbidden();

        $this->assertNull($this->record->refresh()->trained);
    }

    #[Test]
    public function bulk_training_marks_each_member_as_trained()
    {
        $newcomer = factory(User::class)->create(['status' => 'active', 'active' => true]);

        $this->actingAs($this->admin)
            ->post(route('courses.training.bulk-train', $this->course), [
                'user_ids' => [$this->member->id, $newcomer->id],
            ])
            ->assertRedirect(route('courses.training.index', $this->course));

        // The member without a record gets one created
        foreach ([$this->member, $newcomer] as $user) {
            $record = TrainingRecord::where('user_id', $user->id)->where('course_id', $this->course->id)->firstOrFail();
            $this->assertNotNull($record->trained);
            $this->assertEquals($this->admin->id, $record->trainer_user_id);
        }
        Event::assertDispatchedTimes(TrainingRecordCompletedEvent::class, 2);
    }

    #[Test]
    public function bulk_training_rejects_ids_that_are_not_members()
    {
        $this->actingAs($this->admin)
            ->post(route('courses.training.bulk-train', $this->course), [
                'user_ids' => [[$this->member->id]],
            ])
            ->assertSessionHasErrors('user_ids.0');

        $this->assertNull($this->record->refresh()->trained);
    }

    #[Test]
    public function a_member_who_is_not_a_trainer_cannot_bulk_train()
    {
        $this->actingAs(factory(User::class)->create(['status' => 'active', 'active' => true]))
            ->post(route('courses.training.bulk-train', $this->course), [
                'user_ids' => [$this->member->id],
            ])
            ->assertForbidden();

        $this->assertNull($this->record->refresh()->trained);
    }
}
