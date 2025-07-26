<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\Induction;
use BB\Entities\User;
use BB\Events\Inductions\InductionCompletedEvent;
use BB\Events\Inductions\InductionMarkedAsTrainerEvent;
use BB\Repo\InductionRepository;
use BB\Repo\UserRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use BB\Http\Resources\InductionResource;
use BB\Http\Resources\CourseResource;

class CourseTrainingController extends Controller
{
    protected $inductionRepository;
    protected $userRepository;

    public function __construct(InductionRepository $inductionRepository, UserRepository $userRepository)
    {
        $this->inductionRepository = $inductionRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Course $course)
    {
        $this->authorize('viewTraining', $course);

        $trainers = $this->inductionRepository->getTrainersForCourse($course->id);
        $trainers->load('course');

        $trainedUsers = $this->inductionRepository->getTrainedUsersForCourse($course->id);
        $trainedUsers->load('course');

        $usersPendingSignOff = $this->inductionRepository->getUsersPendingSignOffForCourse($course->id);
        $usersPendingSignOff->load('course');

        $memberList = $this->userRepository->getAllAsDropdown();

        return Inertia::render('CourseTraining/Index', [
            'course' => new CourseResource($course),
            'trainers' => InductionResource::collection($trainers),
            'trainedUsers' => InductionResource::collection($trainedUsers),
            'usersPendingSignOff' => InductionResource::collection($usersPendingSignOff),
            'memberList' => collect($memberList)->map(function($name, $id) {
                return ['id' => $id, 'name' => $name];
            })->values(),
            'urls' => [
                'bulkTrain' => route('courses.training.bulk-train', $course, false),
                'back' => route('courses.show', $course, false),
            ],
        ]);
    }


    public function train(Course $course, User $user, Request $request)
    {
        $induction = $this->inductionRepository->getUserForCourse($user->id, $course->id);
        
        if (!$induction) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('train', $induction);

        $trainer = User::findOrFail($request->input('trainer_user_id', auth()->user()->id));

        $induction->update([
            'trained' => Carbon::now(),
            'trainer_user_id' => $trainer->id,
            'sign_off_requested_at' => null,
        ]);

        \Event::dispatch(new InductionCompletedEvent($induction));

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'User marked as trained');
    }

    public function bulkTrain(Course $course, Request $request)
    {
        $this->authorize('train', $course);

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $trainer = auth()->user();
        $trained = [];

        foreach ($request->input('user_ids') as $userId) {
            $induction = $this->inductionRepository->getUserForCourse($userId, $course->id);
            
            if (!$induction) {
                $induction = Induction::create([
                    'user_id' => $userId,
                    'key' => $course->slug,
                    'course_id' => $course->id,
                ]);
            }

            if (!$induction->trained) {
                $induction->update([
                    'trained' => Carbon::now(),
                    'trainer_user_id' => $trainer->id,
                    'sign_off_requested_at' => null,
                ]);

                \Event::dispatch(new InductionCompletedEvent($induction));
                $trained[] = $induction->user->name;
            }
        }

        $message = count($trained) > 0 
            ? 'Marked as trained: ' . implode(', ', $trained)
            : 'No users needed training';

        return redirect()->route('courses.training.index', $course)
            ->with('success', $message);
    }

    public function untrain(Course $course, User $user)
    {
        $induction = $this->inductionRepository->getUserForCourse($user->id, $course->id);
        
        if (!$induction) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('untrain', $induction);

        $induction->update([
            'trained' => null,
            'trainer_user_id' => null,
            'is_trainer' => false, // Also remove trainer status
        ]);

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'Training status removed');
    }

    public function promote(Course $course, User $user)
    {
        $induction = $this->inductionRepository->getUserForCourse($user->id, $course->id);
        
        if (!$induction) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('promote', $induction);

        if (!$induction->trained) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User must be trained before becoming a trainer');
        }

        $induction->update([
            'is_trainer' => true
        ]);

        \Event::dispatch(new InductionMarkedAsTrainerEvent($induction));

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'User promoted to trainer');
    }

    public function demote(Course $course, User $user)
    {
        $induction = $this->inductionRepository->getUserForCourse($user->id, $course->id);
        
        if (!$induction) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('demote', $induction);

        $induction->update([
            'is_trainer' => false
        ]);

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'Trainer status removed');
    }

}