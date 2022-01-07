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
        $obj = $this->model
            ->where('category', $category)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get();

        $arr = array();
        foreach($obj as $d){
            array_push($arr, array(
                "date"=>$d['date'],
                "label"=>$d['label'],
                "value"=>$d['value'], 
            ));
        }

        return $arr;
    }

} 