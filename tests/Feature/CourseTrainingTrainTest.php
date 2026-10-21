<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use Illuminate\Support\Facades\Event;
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

    /** @test */
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

    /** @test */
    public function an_array_of_trainer_ids_is_rejected()
    {
        $this->actingAs($this->admin)
            ->post(route('courses.training.train', [$this->course, $this->member]), [
                'trainer_user_id' => [$this->admin->id],
            ])
            ->assertSessionHasErrors('trainer_user_id');

        $this->assertNull($this->record->refresh()->trained);
    }
}
