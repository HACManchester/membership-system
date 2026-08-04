<?php

namespace BB\Policies;

use BB\Entities\Room;
use BB\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Room $room)
    {
        return true;
    }

    public function create(User $user)
    {
        return $this->canManageRooms($user);
    }

    public function update(User $user, Room $room)
    {
        return $this->canManageRooms($user);
    }

    public function delete(User $user, Room $room)
    {
        return $this->canManageRooms($user);
    }

    public function restore(User $user, Room $room)
    {
        return $this->canManageRooms($user);
    }

    public function forceDelete(User $user, Room $room)
    {
        return $this->canManageRooms($user);
    }

    /**
     * Rooms are equipment infrastructure, so anyone who manages equipment can
     * manage them: admins, the equipment role, maintainers, and area coordinators.
     */
    private function canManageRooms(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasRole('equipment')
            || $user->maintainerGroups()->count() > 0
            || $user->equipmentAreas()->count() > 0;
    }
}
