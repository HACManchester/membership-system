<?php namespace BB\Repo;

use BB\Entities\StorageBox;

class StorageBoxRepository
{
    /**
     * Fetch a members box
     * @param $userId
     * @return StorageBox|null
     */
    public function getMemberBox($userId)
    {
        return StorageBox::findMember($userId);
    }


    /**
     * Return a collection of storage boxes belonging to the user
     *
     * @param integer $userId
     * @return mixed
     */
    public function getMemberBoxes($userId)
    {
        return StorageBox::where('user_id', $userId)->where('active', true)->get();
    }

    /**
     * Get all the active boxes
     * @return mixed
     */
    public function getAll()
    {
        return StorageBox::where('active', 1)->get();
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