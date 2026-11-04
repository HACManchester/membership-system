<?php

namespace BB\Repo;

use BB\Entities\Course;
use BB\Entities\User;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository extends DBRepository
{
    /**
     * @var Course
     */
    protected $model;

    public function __construct(Course $model)
    {
        $this->model = $model;
    }

    /**
     * Get courses filtered by user permissions
     *
     * @param User $user
     * @return Collection
     */
    public function getCoursesForUser(User $user): Collection
    {
        $query = $this->model->with('equipment.courses')->orderBy('name', 'asc');
        
        // Only show live courses to regular users
        if (! $user->can('viewNonLive', Course::class)) {
            $query->where('live', true);
        }
        
        return $query->get();
    }
}
