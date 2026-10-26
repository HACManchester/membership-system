<?php

namespace BB\Listeners;

use BB\Entities\AuditLog;
use BB\Events\SettingUpdated;

class RecordSettingChangeInAuditLog
{
    public function handle(SettingUpdated $event): void
    {
        // The audit log is per member, so the change is filed against the admin who made it.
        AuditLog::create([
            'user_id' => $event->changedBy->id,
            'admin_id' => $event->changedBy->id,
            'action' => 'setting_updated',
            'description' => $event->settingKey->value . ' changed',
        ]);
    }
}
