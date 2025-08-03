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
        $query = $this->model->with('equipment.courses')->orderBy('name', 'ASC');
        
        // Only show live courses to regular users
        if (!$this->canUserSeeNonLiveCourses($user)) {
            $query->where('live', true);
        }
        
        return $query->get();
    }

    /**
     * Check if user can see non-live courses (admins, area coordinators, maintainers)
     *
     * @param User $user
     * @return bool
     */
    public function canUserSeeNonLiveCourses(User $user): bool
    {
        return $user->isAdmin() || 
               $user->equipmentAreas()->count() > 0 || 
               $user->maintainerGroups()->count() > 0;
    }
}