<?php namespace BB\Traits;

trait UserRoleTrait
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\BB\Entities\Role, $this>
     */
    public function roles()
    {
        return $this->belongsToMany(\BB\Entities\Role::class)->withTimestamps();
    }

    /**
     * Does the user have a particular role?
     *
     * @param $name
     * @return bool
     */
    public function hasRole($name)
    {
        foreach ($this->roles as $role) {
            if ($role->name == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Assign a role to the user
     *
     * @param $role
     * @return void
     */
    public function assignRole($role)
    {
        $this->roles()->attach($role);
    }
} 