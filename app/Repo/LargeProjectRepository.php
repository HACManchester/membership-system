<?php namespace BB\Repo;

use BB\Entities\LargeProject;

class LargeProjectRepository extends DBRepository
{

    /**
     * @var LargeProject
     */
    protected $model;

    public function __construct(LargeProject $model)
    {
        $this->model = $model;
    }

    /**
     * Fetch a members box
     * @param $userId
     * @return LargeProject|null
     */
    public function getMemberBox($userId)
    {
        return $this->model->findMember($userId);
    }


    /**
     * Return a collection of storage boxes belonging to the user
     *
     * @param integer $userId
     * @return mixed
     */
    public function getMemberBoxes($userId)
    {
        return $this->model->where('user_id', $userId)->where('active', true)->get();
    }

    /**
     * Get all the active boxes
     * @return mixed
     */
    public function getAll()
    {
        return $this->model->where('active', 1)->get();
    }

    /**
     * Get the number of available boxes
     * @return int
     */
    public function numAvailableBoxes()
    {
        $boxes = $this->getAll();
        $availableBoxes = 0;
        foreach ($boxes as $box) {
            if (empty($box->user_id)) {
                $availableBoxes++;
            }
        }
        return $availableBoxes;
    }


} 