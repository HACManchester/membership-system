<?php namespace BB\Repo;

use BB\Entities\ProfileData;

class ProfileDataRepository extends DBRepository
{

    /**
     * @var ProfileData
     */
    protected $model;

    public function __construct(ProfileData $model)
    {
        $this->model = $model;
    }

    /**
     * Create an empty profile for a user
     * @param integer $userId
     * @return mixed
     */
    public function createProfile($userId)
    {
        $new = new $this->model;
        $new->user_id = $userId;
        $new->save();
        return $new->id;
    }
}
