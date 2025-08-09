<?php

namespace BB\Repo;

use BB\Entities\Induction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InductionRepository extends DBRepository
{
    const LEADERBOARD_THREE_MONTHS = "LEADERBOARD_THREE_MONTHS";
    const LEADERBOARD_YEAR = "LEADERBOARD_YEAR";
    const LEADERBOARD_LAST_YEAR = "LEADERBOARD_LAST_YEAR";
    const LEADERBOARD_ALL_TIME = "LEADERBOARD_ALL_TIME";

    /**
     * @var Induction
     */
    protected $model;

    public function __construct(Induction $model)
    {
        $this->model = $model;
    }

    /**
     * @param $deviceId
     * @return Collection
     */
    public function getTrainersForEquipment($deviceId)
    {
        $trainers = $this->model->with('user', 'user.profile')->where('is_trainer', true)->where('key', $deviceId)->get();
        return $trainers->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    /**
     * @param $userID
     * @return bool
     */
    public function isTrainerForEquipment($user, $deviceId)
    {
        return $this->model->where('user_id', $user->id)
            ->where('key', $deviceId)
            ->where('is_trainer', true)
            ->count() > 0;
    }


    /**
     * @param string $device
     * @return mixed
     */
    public function getTrainedUsersForEquipment($device)
    {
        $users = $this->model->with('user', 'user.profile')->whereNotNull('trained')->where('key', $device)->get();
        return $users->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    /**
     * @param string $device
     * @return mixed
     */
    public function getUsersPendingInductionForEquipment($device)
    {
        $users = $this->model->with('user', 'user.profile')->where('key', $device)->whereNull('trained')->get();
        return $users->filter(function ($trainer) {
            return $trainer->user && $trainer->user->active;
        });
    }

    /**
     * @param $userId
     * @param string $device
     * @return bool
     */
    public function isUserTrained($userId, $device)
    {
        $record = $this->model->with('user', 'user.profile')->whereNotNull('trained')->where('user_id', $userId)->where('key', $device)->first();
        if ($record) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $userId
     * @param string $device
     * @return mixed
     */
    public function getUserForEquipment($userId, $device)
    {
        $record = $this->model->with('user', 'user.profile')->where('user_id', $userId)->where('key', $device)->first();
        if ($record) {
            return $record;
        }
        return false;
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
     * Get all inductions for a user (for frontend comparison with equipment)
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserInductions($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNotNull('trained')
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
    public function getUsersPendingInductionForCourse($courseId)
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
        $expirationHours = Induction::SIGN_OFF_EXPIRATION_HOURS;
        
        $users = $this->model->with('user', 'user.profile')
            ->where('course_id', $courseId)
            ->whereNotNull('sign_off_requested_at')
            ->where('sign_off_requested_at', '>=', Carbon::now()->subHours($expirationHours))
            ->whereNull('trained')
            ->orderBy('sign_off_requested_at', 'asc')
            ->get();
        return $users->filter(function ($induction) {
            return $induction->user && $induction->user->active;
        });
    }
}
