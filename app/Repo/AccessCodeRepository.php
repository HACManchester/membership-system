<?php namespace BB\Repo;

use BB\Entities\AccessCode;

class AccessCodeRepository extends DBRepository
{

    /**
     * @var AccessCode
     */
    protected $model;

    public function __construct(AccessCode $model)
    {
        $this->model = $model;
    }

    /**
     * Updates counter if larger than what is stored
     * @param integer $id           ID of the code
     * @param boolean $counter      New counter value
     */
    public function updateCounter($id, $counter)
    {
        $accessCode = $this->getById($id);

        $maxCounter = max($counter, $accessCode->counter);
        $accessCode->counter = $maxCounter;
        $accessCode->save();
       
        return $maxCounter;
    }


} 