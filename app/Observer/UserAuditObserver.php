<?php namespace BB\Observer;

use BB\Entities\AuditLog;
use BB\Entities\ProfileData;
use BB\Support\CurrentUser;

class UserAuditObserver
{

    /**
     * When a user record is saved look for changes to audit fields
     *   if something was changed log who performed the change and the changing values.
     * @param $user
     */
    public function saved($user)
    {
        $changed  = $user->getDirty();
        $original = $user->getOriginal();


        $changedData = [];

        foreach ($user->getAuditFields() as $field) {
            //See if any audit fields have changed
            if (array_key_exists($field, $changed) && array_key_exists($field, $original)) {
                $changedData[] = $field . ' [is ' . $changed[$field] . ', was ' . $original[$field] . ']';
            }
        }

        $adminId = CurrentUser::find()?->id;

        //If there are changes to log create a new audit log entry with them in
        if (count($changedData) > 0) {
            AuditLog::create(
                [
                    // Observes both User and ProfileData; a profile's own id isn't the member's
                    'user_id'     => $user instanceof ProfileData ? $user->user_id : $user->id,
                    'admin_id'    => $adminId,
                    'action'      => 'user_updated',
                    'description' => implode(', ', $changedData)
                ]
            );
        }
    }

} 