<?php

namespace BB\Events;

use BB\Entities\User;
use BB\Support\SettingKey;
use Illuminate\Queue\SerializesModels;

/**
 * Deliberately carries no value: settings are secrets, and whatever handles
 * this event must not be able to record one.
 */
class SettingUpdated extends Event
{
    use SerializesModels;

    public function __construct(public SettingKey $settingKey, public User $changedBy)
    {
    }
}
