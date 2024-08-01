<?php

namespace BB\FlashNotification;

use Illuminate\Support\Facades\Facade;

class FlashNotificationFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'flash_notification_manager';
    }
}
