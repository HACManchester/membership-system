<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Entities\Equipment;
use BB\Entities\Induction;
use BB\Notifications\Inductions\Inductees\InductionCompletedNotification;
use BB\Notifications\Inductions\Inductees\InductionMarkedAsTrainerNotification;
use BB\Notifications\Inductions\Inductees\InductionRequestedNotification as InducteeInductionRequestedNotification;
use BB\Notifications\Inductions\Trainers\InductionRequestedNotification as TrainerInductionRequestedNotification;

class NotificationPreviewController extends Controller
{
    public function inductionCompleted()
    {
        $user = $this->getDummyUser();
        $induction = $this->getDummyInduction($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new InductionCompletedNotification($induction, $equipment);
        
        return $notification->toMail($user);
    }

    public function inductionMarkedAsTrainer()
    {
        $user = $this->getDummyUser();
        $induction = $this->getDummyInduction($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new InductionMarkedAsTrainerNotification($induction, $equipment);
        
        return $notification->toMail($user);
    }

    public function inducteeInductionRequested()
    {
        $user = $this->getDummyUser();
        $induction = $this->getDummyInduction($user);
        $equipment = $this->getDummyEquipment();
        
        $notification = new InducteeInductionRequestedNotification($induction, $equipment);
        
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
        $induction = $this->getDummyInduction($inductee);
        $equipment = $this->getDummyEquipment();
        
        $notification = new TrainerInductionRequestedNotification($induction, $equipment);
        
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

    private function getDummyInduction(User $user)
    {
        $induction = new Induction([
            'id' => 1,
            'user_id' => $user->id,
            'inducted_by' => null,
            'is_trainer' => false,
            'inducted_at' => null,
        ]);
        $induction->setRelation('user', $user);
        return $induction;
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
}