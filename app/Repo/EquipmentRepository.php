<?php namespace BB\Repo;

use BB\Entities\Equipment;
use Illuminate\Support\Collection;

class EquipmentRepository extends DBRepository
{

    /**
     * @var Equipment
     */
    protected $model;

    public function __construct(Equipment $model)
    {
        $this->model = $model;
    }

    /**
     * Get equipment that requires induction but doesn't have a live course
     *
     * @return Collection
     */
    public function getEquipmentWithoutLiveCourse()
    {
        return $this->model->with('roomModel')
            ->where('requires_induction', true)
            ->where(function ($query) {
                // Equipment without any courses
                $query->whereDoesntHave('courses')
                    // Or equipment with courses that are not live
                    ->orWhereHas('courses', function ($q) {
                        $q->where('live', false);
                    });
            })
            ->whereDoesntHave('courses', function ($q) {
                // Exclude equipment that has at least one live course
                $q->where('live', true);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get equipment available for course creation
     *
     * @return Collection
     */
    public function getEquipmentForCourseCreation()
    {
        return $this->model->with('roomModel')
            ->whereDoesntHave('courses')
            ->where(function ($query) {
                $query->whereNull('induction_category')
                    ->orWhere('induction_category', '');
            })
            ->orderBy('name')
            ->get();
    }
} 
