<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Events\TrainingRecords\TrainingInterestRegisteredEvent;
use BB\Events\TrainingRecords\TrainingInterestWithdrawnEvent;
use BB\Http\Requests\RegisterCourseInterestRequest;
use BB\Http\Requests\WithdrawCourseInterestRequest;
use Illuminate\Support\Facades\DB;

/**
 * Members registering and withdrawing interest in training for a course. An
 * interest registration is a bare TrainingRecord (no trained/sign-off dates);
 * created_at records when the member registered, and the trainer-facing list
 * is ordered by it. Deliberately not promised to members as a queue — how
 * trainers use the list varies by course.
 */
class CourseInterestController extends Controller
{
    public function store(RegisterCourseInterestRequest $request, Course $course)
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($user, $course) {
            $trainingRecord = TrainingRecord::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ],
                [
                    'key' => $course->slug, // Still populate for backwards compatibility
                ]
            );

            if ($trainingRecord->trained) {
                return ['error' => 'You are already trained for this course'];
            }

            if ($trainingRecord->wasRecentlyCreated) {
                \Event::dispatch(new TrainingInterestRegisteredEvent($trainingRecord));
            }

            return ['success' => true];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()
            ->with('success', 'Your interest has been registered. Trainers use this list when organising training sessions.');
    }

    public function destroy(WithdrawCourseInterestRequest $request, Course $course)
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($user, $course) {
            $trainingRecord = TrainingRecord::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if (! $trainingRecord) {
                return ['error' => 'You have not registered interest in this course'];
            }

            if ($trainingRecord->trained) {
                return ['error' => 'You are already trained for this course'];
            }

            if ($trainingRecord->is_trainer) {
                return ['error' => 'Trainers cannot withdraw their interest in this course'];
            }

            if ($trainingRecord->sign_off_requested_at && ! $trainingRecord->isSignOffExpired()) {
                return ['error' => 'You have a pending sign-off request for this course'];
            }

            $trainingRecord->delete();

            \Event::dispatch(new TrainingInterestWithdrawnEvent($user, $course));

            return ['success' => true];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()
            ->with('success', 'Your interest has been withdrawn');
    }
}
