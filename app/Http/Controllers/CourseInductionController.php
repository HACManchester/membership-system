<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\Induction;
use BB\Events\Inductions\InductionRequestedEvent;
use Illuminate\Support\Facades\DB;

class CourseInductionController extends Controller
{
    /**
     * Used when members request sign off after completing a quiz or in-person training session
     */
    public function store(Course $course)
    {
        $user = auth()->user();

        // Authorize that user can request sign-off for this course
        $this->authorize('requestSignOff', $course);

        if ($course->isPaused()) {
            return redirect()->back()
                ->with('error', 'This course is currently paused');
        }

        // Use database transaction to prevent race conditions
        $result = DB::transaction(function () use ($user, $course) {
            $induction = Induction::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ],
                [
                    'key' => $course->slug, // Still populate for backwards compatibility
                ]
            );

            if ($induction->trained) {
                return ['error' => 'You are already trained for this course'];
            }

            $induction->update([
                'sign_off_requested_at' => now()
            ]);

            if ($induction->wasRecentlyCreated || $induction->isSignOffExpired()) {
                \Event::dispatch(new InductionRequestedEvent($induction));
            }

            return ['success' => true];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->back()
            ->with('success', 'Sign-off request submitted. A trainer will review your request shortly.');
    }
}
