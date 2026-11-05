<?php

namespace BB\Entities\Concerns;

use BB\Entities\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Does the user have a particular role?
     */
    public function hasRole($name): bool
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

    /**
     * Is the user part of the admin group
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
