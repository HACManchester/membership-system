<?php namespace BB\Repo;

use BB\Entities\Equipment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function all()
    {
        return $this->model->all();
    }

    public function getByRoom()
    {
        return $this->model->groupBy('room')->get();
    }

    public function getRequiresInduction()
    {
        return $this->model->where('requires_induction', true)->get();
    }

    public function getDoesntRequireInduction()
    {
        return $this->model->where('requires_induction', false)->get();
    }


    public function allPaid()
    {
        return $this->model->where('access_fee', '!=', 0)->get();
    }

    /**
     * Return a device by its slug
     * @param $slug
     * @return Equipment
     */
    public function findBySlug($slug)
    {
        $record = $this->model->where('slug', $slug)->first();
        if ($record) {
            return $record;
        }
        throw new ModelNotFoundException();
    }

    /**
     * @param $induction_category
     * @return Collection
     */
    public function findByInductionCategory($induction_category)
    {
        return $this->model->where('induction_category', $induction_category)->get();
    }

    /**
     * Get equipment that requires induction but doesn't have a live course
     *
     * @return Collection
     */
    public function getEquipmentWithoutLiveCourse()
    {
        return $this->model->where('requires_induction', true)
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
        return $this->model->whereDoesntHave('courses')
            ->where(function ($query) {
                $query->whereNull('induction_category')
                    ->orWhere('induction_category', '');
            })
            ->orderBy('name')
            ->get();
    }
} 
