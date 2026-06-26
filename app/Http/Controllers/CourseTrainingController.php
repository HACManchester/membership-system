<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use BB\Events\TrainingRecords\TrainingRecordCompletedEvent;
use BB\Events\TrainingRecords\TrainingRecordMarkedAsTrainerEvent;
use BB\Repo\TrainingRecordRepository;
use BB\Repo\UserRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use BB\Http\Resources\TrainingRecordResource;
use BB\Http\Resources\CourseResource;

class CourseTrainingController extends Controller
{
    protected $trainingRecordRepository;
    protected $userRepository;

    public function __construct(TrainingRecordRepository $trainingRecordRepository, UserRepository $userRepository)
    {
        $this->trainingRecordRepository = $trainingRecordRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Course $course)
    {
        $this->authorize('viewTraining', $course);

        $trainers = $this->trainingRecordRepository->getTrainersForCourse($course->id);
        $trainers->load(['course', 'user.profile']);

        $trainedUsers = $this->trainingRecordRepository->getTrainedUsersForCourse($course->id);
        $trainedUsers->load(['course', 'user.profile']);

        $usersPendingSignOff = $this->trainingRecordRepository->getUsersPendingSignOffForCourse($course->id);
        $usersPendingSignOff->load(['course', 'user.profile']);

        $memberList = $this->userRepository->getAllAsDropdown();

        return Inertia::render('CourseTraining/Index', [
            'course' => new CourseResource($course),
            'trainers' => TrainingRecordResource::collection($trainers),
            'trainedUsers' => TrainingRecordResource::collection($trainedUsers),
            'usersPendingSignOff' => TrainingRecordResource::collection($usersPendingSignOff),
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
        $trainingRecord = $this->trainingRecordRepository->getUserForCourse($user->id, $course->id);
        
        if (!$trainingRecord) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('train', $trainingRecord);

        $trainer = User::findOrFail($request->input('trainer_user_id', auth()->user()->id));

        $trainingRecord->update([
            'trained' => Carbon::now(),
            'trainer_user_id' => $trainer->id,
            'sign_off_requested_at' => null,
        ]);

        \Event::dispatch(new TrainingRecordCompletedEvent($trainingRecord));

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
            $trainingRecord = $this->trainingRecordRepository->getUserForCourse($userId, $course->id);
            
            if (!$trainingRecord) {
                $trainingRecord = TrainingRecord::create([
                    'user_id' => $userId,
                    'key' => $course->slug,
                    'course_id' => $course->id,
                ]);
            }

            if (!$trainingRecord->trained) {
                $trainingRecord->update([
                    'trained' => Carbon::now(),
                    'trainer_user_id' => $trainer->id,
                    'sign_off_requested_at' => null,
                ]);

                \Event::dispatch(new TrainingRecordCompletedEvent($trainingRecord));
                $trained[] = $trainingRecord->user->name;
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
        $trainingRecord = $this->trainingRecordRepository->getUserForCourse($user->id, $course->id);
        
        if (!$trainingRecord) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('untrain', $trainingRecord);

        $trainingRecord->update([
            'trained' => null,
            'trainer_user_id' => null,
            'is_trainer' => false, // Also remove trainer status
        ]);

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'Training status removed');
    }

    public function promote(Course $course, User $user)
    {
        $trainingRecord = $this->trainingRecordRepository->getUserForCourse($user->id, $course->id);
        
        if (!$trainingRecord) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('promote', $trainingRecord);

        if (!$trainingRecord->trained) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User must be trained before becoming a trainer');
        }

        $trainingRecord->update([
            'is_trainer' => true
        ]);

        \Event::dispatch(new TrainingRecordMarkedAsTrainerEvent($trainingRecord));

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'User promoted to trainer');
    }

    public function demote(Course $course, User $user)
    {
        $trainingRecord = $this->trainingRecordRepository->getUserForCourse($user->id, $course->id);
        
        if (!$trainingRecord) {
            return redirect()->route('courses.training.index', $course)
                ->with('error', 'User does not have an induction record for this course');
        }

        $this->authorize('demote', $trainingRecord);

        $trainingRecord->update([
            'is_trainer' => false
        ]);

        return redirect()->route('courses.training.index', $course)
            ->with('success', 'Trainer status removed');
    }

}