<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Entities\Equipment;
use BB\Entities\TrainingRecord;
use BB\Entities\Course;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordCompletedNotification;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordMarkedAsTrainerNotification;
use BB\Notifications\TrainingRecords\Inductees\TrainingRecordRequestedNotification as InducteeInductionRequestedNotification;
use BB\Notifications\TrainingRecords\Trainers\TrainingRecordRequestedNotification as TrainerInductionRequestedNotification;

class NotificationPreviewController extends Controller
{
    public function inductionCompleted()
    {
        $user = $this->getDummyUser();
        $trainingRecord = $this->getDummyTrainingRecord($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainingRecordCompletedNotification($trainingRecord, $equipment);
        
        return $notification->toMail($user);
    }

    public function inductionMarkedAsTrainer()
    {
        $user = $this->getDummyUser();
        $trainingRecord = $this->getDummyTrainingRecord($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainingRecordMarkedAsTrainerNotification($trainingRecord, $equipment);
        
        return $notification->toMail($user);
    }

    public function inducteeInductionRequested()
    {
        $user = $this->getDummyUser();
        $trainingRecord = $this->getDummyTrainingRecord($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new InducteeInductionRequestedNotification($trainingRecord, $equipment);
        
        return $notification->toMail($user);
    }

    public function trainerInductionRequested()
    {
        $trainer = $this->getDummyUser();
        $inductee = factory(User::class)->make([
            'id' => 998,
            'given_name' => 'Jane',
            'family_name' => 'Smith',
            'status' => 'active',
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);
        $trainingRecord = $this->getDummyTrainingRecord($inductee);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainerInductionRequestedNotification($trainingRecord, $equipment);
        
        return $notification->toMail($trainer);
    }

    // Course-based notification previews
    public function courseInductionCompleted()
    {
        $user = $this->getDummyUser();
        $trainingRecord = $this->getDummyCourseTrainingRecord($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainingRecordCompletedNotification($trainingRecord, $equipment);
        
        return $notification->toMail($user);
    }

    public function courseInductionMarkedAsTrainer()
    {
        $user = $this->getDummyUser();
        $trainingRecord = $this->getDummyCourseTrainingRecord($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainingRecordMarkedAsTrainerNotification($trainingRecord, $equipment);
        
        return $notification->toMail($user);
    }

    public function courseInducteeInductionRequested()
    {
        $user = $this->getDummyUser();
        $trainingRecord = $this->getDummyCourseTrainingRecord($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new InducteeInductionRequestedNotification($trainingRecord, $equipment);
        
        return $notification->toMail($user);
    }

    public function courseTrainerInductionRequested()
    {
        $trainer = $this->getDummyUser();
        $inductee = factory(User::class)->make([
            'id' => 998,
            'given_name' => 'Jane',
            'family_name' => 'Smith',
            'status' => 'active',
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);
        $trainingRecord = $this->getDummyCourseTrainingRecord($inductee);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainerInductionRequestedNotification($trainingRecord, $equipment);
        
        return $notification->toMail($trainer);
    }

    private function getDummyUser()
    {
        return factory(User::class)->make([
            'id' => 999,
            'status' => 'active',
            'active' => true,
            'hash' => \Illuminate\Support\Str::random(32),
        ]);
    }

    private function getDummyTrainingRecord(User $user)
    {
        $trainingRecord = new TrainingRecord([
            'id' => 1,
            'user_id' => $user->id,
            'inducted_by' => null,
            'is_trainer' => false,
            'inducted_at' => null,
        ]);
        $trainingRecord->setRelation('user', $user);
        return $trainingRecord;
    }

    private function getDummyEquipment()
    {
        $laserCutter = factory(Equipment::class)->make([
            'id' => 1,
            'name' => 'Laser Cutter',
            'slug' => 'laser-cutter',
            'induction_instructions' => 'Contact the laser team on Telegram to arrange training',
            'trained_instructions' => 'Remember to always check the extraction is running before use',
        ]);

        $printer3d = factory(Equipment::class)->make([
            'id' => 2,
            'name' => '3D Printer',
            'slug' => '3d-printer',
            'induction_instructions' => 'Weekly training sessions every Wednesday at 7pm',
            'trained_instructions' => '',
        ]);

        return collect([$laserCutter, $printer3d]);
    }

    private function getDummyCourseTrainingRecord(User $user)
    {
        $course = factory(Course::class)->make([
            'id' => 1,
            'name' => 'Laser Cutting',
            'slug' => 'laser-cutting',
            'description' => 'Learn how to safely operate our laser cutters for precision cutting and engraving.',
            'format' => 'group',
            'format_description' => 'Group training sessions with hands-on practice',
            'frequency' => 'regular',
            'training_organisation_description' => 'Training sessions run every Tuesday at 7pm and Saturday at 2pm. Maximum 4 people per session.',
            'wait_time' => '1-2 weeks',
        ]);

        $trainingRecord = new TrainingRecord([
            'id' => 1,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'inducted_by' => null,
            'is_trainer' => false,
            'inducted_at' => null,
        ]);
        $trainingRecord->setRelation('user', $user);
        $trainingRecord->setRelation('course', $course);
        
        return $trainingRecord;
    }
}