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
     * @return array
     */
    public function getTrainersByEquipment()
    {
        $trainersRaw = $this->model->with('user', 'user.profile')->where('is_trainer', true)->get();
        $trainers = [];
        foreach ($trainersRaw as $trainer) {
            if (isset($trainer->user->name) && $trainer->user->active) {
                $trainers[$trainer->key][] = $trainer->user;
            }
        }
        return $trainers;
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
            ->where('is_trainer', 1)
            ->count() > 0;
    }

    /**
     * Get all the users who have been trained on a piece of equipment
     * @param string $deviceId
     * @return Collection
     */
    public function getUsersForEquipment($deviceId)
    {
        $users = new Collection();
        $inductionUsers = $this->model->with('user')->whereHas('user', function ($q) {
            $q->where('active', '=', true);
        })->where('trained', '!=', '')->where('key', $deviceId)->get();

        //Extract the users from the inductions and place into a new collection
        foreach ($inductionUsers as $inductedUser) {
            $users->add($inductedUser->user);
        }
        return $users;
    }


    /**
     * @return array
     */
    public function getUsersPendingInduction()
    {
        $usersRaw = $this->model->with('user', 'user.profile')->where('paid', true)->whereNull('trained')->get();
        $users = [];
        foreach ($usersRaw as $induction) {
            if (isset($induction->user->name) && $induction->user->active) {
                $users[$induction->key][] = $induction->user;
            }
        }
        return $users;
    }

    /**
     * @return array
     */
    public function getTrainedUsers()
    {
        $usersRaw = $this->model->with('user', 'user.profile')->where('paid', true)->whereNotNull('trained')->get();
        $users = [];
        foreach ($usersRaw as $induction) {
            if (isset($induction->user->name) && $induction->user->active) {
                $users[$induction->key][] = $induction->user;
            }
        }
        return $users;
    }

    /**
     * @param string $device
     * @return mixed
     */
    public function getTrainedUsersForEquipment($device)
    {
        $users = $this->model->with('user', 'user.profile')->where('paid', true)->whereNotNull('trained')->where('key', $device)->get();
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
        $users = $this->model->with('user', 'user.profile')->where('paid', true)->where('key', $device)->whereNull('trained')->get();
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
        $record = $this->model->with('user', 'user.profile')->where('paid', true)->whereNotNull('trained')->where('user_id', $userId)->where('key', $device)->first();
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

    /**
     * Fetch an induction record by its associated payment
     * @param $paymentId
     * @return mixed
     */
    public function getByPaymentId($paymentId)
    {
        return $this->model->where('payment_id', $paymentId)->first();
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
}
