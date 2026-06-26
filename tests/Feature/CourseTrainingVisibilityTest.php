<?php

namespace Tests\Feature;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Repo\TrainingRecordRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the bug where members trained under the modern course system were
 * reported as "not trained" by surfaces that only matched the legacy
 * induction_category↔key path. The scenarios below deliberately use a course
 * whose slug differs from the equipment's induction_category — the case the
 * old key-only match got wrong.
 */
class CourseTrainingVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $equipment;
    protected $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = factory(User::class)->state('admin')->create();

        // Equipment whose legacy category ('legacy-cat') is NOT the course slug.
        $this->equipment = factory(Equipment::class)->create([
            'name' => 'Laser Cutter',
            'slug' => 'laser-cutter',
            'requires_induction' => true,
            'induction_category' => 'legacy-cat',
            'accepting_inductions' => true,
            'access_code' => 'COURSECODE',
        ]);

        $this->course = factory(Course::class)->create(['slug' => 'modern-course']);
        $this->course->equipment()->attach($this->equipment);
    }

    /**
     * A training record created by the modern course flow: course_id set, key
     * set to the course slug (as CourseInductionController / CourseTrainingController do).
     */
    private function courseTrainingRecord(User $user, bool $trained = true, bool $isTrainer = false): TrainingRecord
    {
        $record = new TrainingRecord([
            'user_id' => $user->id,
            'key' => $this->course->slug,
            'course_id' => $this->course->id,
            'trained' => $trained ? now() : null,
            'active' => true,
            'is_trainer' => $isTrainer,
            'trainer_user_id' => $this->admin->id,
        ]);
        $record->save();

        return $record;
    }

    /** @test */
    public function account_page_shows_course_trained_member_as_trained()
    {
        $user = factory(User::class)->create();
        $this->courseTrainingRecord($user);

        $response = $this->actingAs($user)->get(route('account.show', $user->id));

        $response->assertStatus(200);
        // The access code is only rendered for trained members, so seeing it
        // proves the page recognised the course-based training record.
        $response->assertSee('COURSECODE');
    }

    /** @test */
    public function account_page_hides_access_code_when_member_has_no_training_record()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('account.show', $user->id));

        $response->assertStatus(200);
        $response->assertDontSee('COURSECODE');
    }

    /** @test */
    public function equipment_index_shows_access_code_to_course_trained_member()
    {
        $user = factory(User::class)->create();
        $this->courseTrainingRecord($user);

        $response = $this->actingAs($user)->get(route('equipment.index'));

        $response->assertStatus(200);
        $response->assertSee('COURSECODE');
    }

    /** @test */
    public function equipment_show_shows_access_code_to_course_trained_member()
    {
        $user = factory(User::class)->create();
        $this->courseTrainingRecord($user);

        $response = $this->actingAs($user)->get(route('equipment.show', $this->equipment));

        $response->assertStatus(200);
        $response->assertSee('COURSECODE');
    }

    /** @test */
    public function repository_matches_records_via_both_course_and_legacy_key()
    {
        $repo = app(TrainingRecordRepository::class);

        // Modern: course-trained member.
        $courseUser = factory(User::class)->create();
        $this->courseTrainingRecord($courseUser);

        // Legacy: trained purely via the induction_category↔key match.
        $legacyUser = factory(User::class)->create();
        (new TrainingRecord([
            'user_id' => $legacyUser->id,
            'key' => 'legacy-cat',
            'trained' => now(),
            'active' => true,
            'trainer_user_id' => $this->admin->id,
        ]))->save();

        $equipment = Equipment::find($this->equipment->id);

        $this->assertTrue($repo->isUserTrained($equipment, $courseUser->id));
        $this->assertTrue($repo->isUserTrained($equipment, $legacyUser->id));

        $trainedIds = $repo->getTrainedUsersForEquipment($equipment)
            ->map(function ($record) {
                return $record->user_id;
            })
            ->sort()
            ->values()
            ->all();
        $this->assertEquals([$courseUser->id, $legacyUser->id], $trainedIds);

        $this->assertNotFalse($repo->getUserForEquipment($equipment, $courseUser->id));
    }

    /** @test */
    public function equipment_with_no_category_or_course_matches_no_records()
    {
        $repo = app(TrainingRecordRepository::class);

        $orphan = factory(Equipment::class)->create([
            'slug' => 'orphan',
            'requires_induction' => true,
            'induction_category' => null,
        ]);

        // An unrelated record that must not leak through the empty-where guard.
        $someoneElse = factory(User::class)->create();
        $this->courseTrainingRecord($someoneElse);

        $this->assertFalse($repo->isUserTrained($orphan, $someoneElse->id));
        $this->assertCount(0, $repo->getTrainedUsersForEquipment($orphan));
    }

    /** @test */
    public function trainer_signed_off_via_course_can_train_and_request_for_others()
    {
        $trainer = factory(User::class)->create();
        $this->courseTrainingRecord($trainer, true, true);

        $other = factory(User::class)->create();
        $equipment = Equipment::find($this->equipment->id);

        // EquipmentPolicy::train and TrainingRecordPolicy::create both gate on
        // trainer status — the course-based trainer must satisfy them.
        $this->assertTrue($trainer->can('train', $equipment));
        $this->assertTrue($trainer->can('create', [TrainingRecord::class, $equipment, $other]));

        // A member who isn't a trainer must not.
        $this->assertFalse($other->can('train', $equipment));
    }
}
