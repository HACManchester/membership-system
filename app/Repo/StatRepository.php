<?php namespace BB\Repo;

use BB\Entities\Stat;

class StatRepository extends DBRepository
{

    /**
     * @var Stat
     */
    protected $model;

    public function __construct(Stat $model)
    {
        $this->model = $model;
    }

    public function getCategoryDates($category, $start, $end)
    {
        return $this->model
            ->where('category', $category)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get();
    }

} 