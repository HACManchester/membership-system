<?php

namespace BB\Http\Controllers;

use BB\Entities\Equipment;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use BB\Events\TrainingRecords\TrainingRecordMarkedAsTrainerEvent;
use BB\Events\TrainingRecords\TrainingRecordRequestedEvent;
use BB\Http\Requests\StoreTrainingRecordRequest;
use BB\Http\Requests\TrainTrainingRecordRequest;

class TrainingRecordController extends Controller
{
    public function store(Equipment $equipment, StoreTrainingRecordRequest $request)
    {
        $userId = $request->input('user_id', \Auth::user()->id);

        $course = $equipment->courses->first();

        $trainingRecord = TrainingRecord::create([
            'user_id' => $userId,
            'key' => $equipment->induction_category, // Keep for backwards compatibility
            'course_id' => $course ? $course->id : null,
        ]);

        \Event::dispatch(new TrainingRecordRequestedEvent($trainingRecord));

        return \Redirect::route('equipment.show', $equipment);
    }

    public function train(Equipment $equipment, TrainingRecord $trainingRecord, TrainTrainingRecordRequest $request)
    {
        $trainer = User::findOrFail($request->input('trainer_user_id'));

        $trainingRecord->update([
            'trained' => \Carbon\Carbon::now(),
            'trainer_user_id' => $trainer->id,
        ]);

        \Event::dispatch(new TrainingRecordCompletedEvent($trainingRecord));

        return \Redirect::route('equipment.show', $equipment);
    }

    public function untrain(Equipment $equipment, TrainingRecord $trainingRecord)
    {
        $this->authorize('untrain', $trainingRecord);

        $trainingRecord->update([
            'trained' => null,
            'trainer_user_id' => 0
        ]);

        return \Redirect::route('equipment.show', $equipment);
    }

    public function promote(Equipment $equipment, TrainingRecord $trainingRecord)
    {
        $this->authorize('promote', $trainingRecord);

        $trainingRecord->update([
            'is_trainer' => true
        ]);

        \Event::dispatch(new TrainingRecordMarkedAsTrainerEvent($trainingRecord));

        return \Redirect::route('equipment.show', $equipment);
    }

    public function demote(Equipment $equipment, TrainingRecord $trainingRecord)
    {
        $this->authorize('demote', $trainingRecord);

        $trainingRecord->update([
            'is_trainer' => false
        ]);

        return \Redirect::route('equipment.show', $equipment);
    }

    public function destroy(Equipment $equipment, TrainingRecord $trainingRecord)
    {
        $this->authorize('delete', $trainingRecord);

        $trainingRecord->delete();

        return \Redirect::route('equipment.show', $equipment);
    }
}
