<?php namespace BB\Repo;

use BB\Entities\EquipmentLog;
use BB\Exceptions\DeviceException;
use BB\Exceptions\ValidationException;
use Carbon\Carbon;

class EquipmentLogRepository extends DBRepository
{
    // TODO: Remove this, no usages in production.

    /**
     * @var EquipmentLog
     */
    protected $model;

    public function __construct(EquipmentLog $model)
    {
        $this->model = $model;
        $this->perPage = 25;
    }

    /**
     * Return records that have been checked over
     * @param $deviceKey
     * @return mixed
     */
    public function getFinishedForEquipment($deviceKey)
    {
        return $this->model->where('device', $deviceKey)->where('active', false)->where('removed', false)->orderBy('created_at', 'DESC')->paginate($this->perPage);
    }

    /**
     * @param string $deviceKey
     * @param bool   $billedTime
     * @param null   $reason
     * @return int
     */
    public function getTotalTime($deviceKey, $billedTime = null, $reason = null)
    {
        $totalTime = 0;
        $query     = $this->model->where('device', $deviceKey)->where('active', false);

        if ($billedTime !== null) {
            $query = $query->where('billed', $billedTime);
        }
        if ($reason !== null) {
            $query = $query->where('reason', $reason);
        }

        // todo: will throw exception due to missing orderBy?
        // todo: unused functionality? Bin off?
        $query->chunk(100, function ($results) use (&$totalTime) {
            /** @var EquipmentLog[] $results */
            foreach ($results as $result) {
                if ($result->started instanceof Carbon) {
                    $totalTime += $result->started->diffInSeconds($result->finished);
                }
            }
        });

        return (int) ($totalTime / 60);
    }

    /**
     * Return all records that have been checked over but not billed
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFinishedUnbilledRecords()
    {
        return $this->model->where('active', false)->where('removed', false)->where('billed', false)->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get all records that haven't been billed yet
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnbilledRecords()
    {
        return $this->model->where('active', false)->where('billed', false)->orderBy('created_at', 'DESC')->get();
    }

} 