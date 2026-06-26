<?php

namespace BB\Repo;

use BB\Entities\Equipment;
use BB\Entities\TrainingRecord;
use BB\Entities\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrainingRecordRepository extends DBRepository
{
    const LEADERBOARD_THREE_MONTHS = "LEADERBOARD_THREE_MONTHS";
    const LEADERBOARD_YEAR = "LEADERBOARD_YEAR";
    const LEADERBOARD_LAST_YEAR = "LEADERBOARD_LAST_YEAR";
    const LEADERBOARD_ALL_TIME = "LEADERBOARD_ALL_TIME";

    /**
     * @var TrainingRecord
     */
    protected $model;

    public function __construct(TrainingRecord $model)
    {
        $this->model = $model;
    }

    /**
     * Build a query matching every training record tied to a piece of equipment,
     * covering both linkage paths: the legacy induction_category↔key string match
     * and the modern course_equipment↔course_id relationship. Records trained under
     * the course system would otherwise be missed by a key-only match (their key is
     * the course slug, not necessarily the equipment's induction_category).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function recordsForEquipmentQuery(Equipment $equipment)
    {
        $courseIds = $equipment->courses->pluck('id');
        $hasCategory = ! empty($equipment->induction_category);

        // Equipment with no course links and no induction_category has nothing to
        // match against — return a query that yields no records (an unguarded empty
        // where() closure would otherwise match every training record).
        if ($courseIds->isEmpty() && ! $hasCategory) {
            return $this->model->whereRaw('1 = 0');
        }

        return $this->model->where(function ($query) use ($equipment, $courseIds, $hasCategory) {
            if ($courseIds->isNotEmpty()) {
                $query->orWhereIn('course_id', $courseIds);
            }
            if ($hasCategory) {
                $query->orWhere('key', $equipment->induction_category);
            }
        });
    }

    /**
     * @return Collection
     */
    public function getTrainersForEquipment(Equipment $equipment)
    {
        $trainers = $this->recordsForEquipmentQuery($equipment)
            ->with('user', 'user.profile')
            ->where('is_trainer', true)
            ->get();
        return $trainers->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    /**
     * Trainers matched by the legacy induction key alone. Used by the
     * training-request listener when notifying trainers for a legacy
     * (course-less) record.
     *
     * @return Collection
     */
    public function getTrainersForKey($key)
    {
        $trainers = $this->model->with('user', 'user.profile')
            ->where('is_trainer', true)
            ->where('key', $key)
            ->get();
        return $trainers->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    public function isTrainerForEquipment(User $user, Equipment $equipment): bool
    {
        return $this->recordsForEquipmentQuery($equipment)
            ->where('user_id', $user->id)
            ->where('is_trainer', true)
            ->exists();
    }

    /**
     * Trainer check by legacy induction key alone.
     */
    public function isTrainerForKey(User $user, $key): bool
    {
        return $this->model->where('user_id', $user->id)
            ->where('key', $key)
            ->where('is_trainer', true)
            ->exists();
    }

    /**
     * Whether the user is a trainer for the same equipment/course as the given
     * training record — course-based when the record has a course, falling back
     * to the legacy key match otherwise. Mirrors the dual linkage so a trainer
     * signed off under the modern system isn't missed.
     */
    public function isTrainerForRecord(User $user, TrainingRecord $record): bool
    {
        if ($record->course_id) {
            return $this->isTrainerForCourse($user, $record->course_id);
        }

        return $this->isTrainerForKey($user, $record->key);
    }

    public function getTrainedUsersForEquipment(Equipment $equipment)
    {
        $users = $this->recordsForEquipmentQuery($equipment)
            ->with('user', 'user.profile')
            ->whereNotNull('trained')
            ->get();
        return $users->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    public function getUsersPendingTrainingForEquipment(Equipment $equipment)
    {
        $users = $this->recordsForEquipmentQuery($equipment)
            ->with('user', 'user.profile')
            ->whereNull('trained')
            ->get();
        return $users->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    public function isUserTrained(Equipment $equipment, int $userId): bool
    {
        return $this->recordsForEquipmentQuery($equipment)
            ->whereNotNull('trained')
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * @return TrainingRecord|false
     */
    public function getUserForEquipment(Equipment $equipment, int $userId)
    {
        return $this->recordsForEquipmentQuery($equipment)
            ->with('user', 'user.profile')
            ->where('user_id', $userId)
            ->first() ?? false;
    }

    public function getLeaderboard($timePeriod)
    {
        $baseQuery = $this->model
            ->groupBy('trainer_user_id')
            ->select('trainer_user_id', DB::raw('count(*) as total'))
            ->where('trainer_user_id', '!=', 0);

        switch ($timePeriod) {
            case static::LEADERBOARD_THREE_MONTHS:
                $baseQuery->where('trained', '>=', Carbon::now()->subMonths(3)->format('Y-m-d'));
                break;

            case static::LEADERBOARD_YEAR:
                $baseQuery->where(DB::raw('YEAR(trained)'), '>=', Carbon::now()->year);
                break;

            case static::LEADERBOARD_LAST_YEAR:
                $baseQuery->where(DB::raw('YEAR(trained)'), '>=', Carbon::now()->year - 1)
                    ->where(DB::raw('YEAR(trained)'), '<', Carbon::now()->year);
                break;

            case static::LEADERBOARD_ALL_TIME:
                // no-op
                break;
        }

        return $baseQuery
            ->orderBy('total', 'desc')
            ->orderBy('trainer_user_id', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Course-based methods for new system
     */
    
    /**
     * @param int $courseId
     * @return Collection
     */
    public function getTrainersForCourse($courseId)
    {
        $trainers = $this->model->with('user', 'user.profile', 'trainerUser')
            ->where('is_trainer', true)
            ->where('course_id', $courseId)
            ->get();
        return $trainers->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }
    
    /**
     * @param $user
     * @param int $courseId
     * @return bool
     */
    public function isTrainerForCourse($user, $courseId)
    {
        return $this->model->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('is_trainer', true)
            ->count() > 0;
    }
    
    /**
     * @param int $courseId
     * @return mixed
     */
    public function getTrainedUsersForCourse($courseId)
    {
        $users = $this->model->with('user', 'user.profile', 'trainerUser')
            ->whereNotNull('trained')
            ->where('course_id', $courseId)
            ->orderBy('trained', 'desc')
            ->get();
        return $users->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }
    
    /**
     * @param int $courseId
     * @return mixed
     */
    public function getUsersPendingTrainingForCourse($courseId)
    {
        $users = $this->model->with('user', 'user.profile')
            ->where('course_id', $courseId)
            ->whereNull('trained')
            ->orderBy('created_at', 'desc')
            ->get();
        return $users->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }
    
    /**
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    public function isUserTrainedForCourse($userId, $courseId)
    {
        $record = $this->model->with('user', 'user.profile')
            ->whereNotNull('trained')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
        return (bool)$record;
    }
    
    /**
     * @param int $userId
     * @param int $courseId
     * @return mixed
     */
    public function getUserForCourse($userId, $courseId)
    {
        $record = $this->model->with('user', 'user.profile')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
        if ($record) {
            return $record;
        }
        return false;
    }
    
    /**
     * Get users with pending sign-off requests for a course (within expiration window)
     * 
     * @param int $courseId
     * @return Collection
     */
    public function getUsersPendingSignOffForCourse($courseId)
    {
        $expirationHours = TrainingRecord::SIGN_OFF_EXPIRATION_HOURS;
        
        $users = $this->model->with('user', 'user.profile')
            ->where('course_id', $courseId)
            ->whereNotNull('sign_off_requested_at')
            ->where('sign_off_requested_at', '>=', Carbon::now()->subHours($expirationHours))
            ->whereNull('trained')
            ->orderBy('sign_off_requested_at', 'asc')
            ->get();
        return $users->filter(function ($trainingRecord) {
            return $trainingRecord->user && $trainingRecord->user->active;
        });
    }
}
