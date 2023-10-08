<?php

namespace BB\Helpers;

use Illuminate\Notifications\Notifiable;

class TelegramErrorHelper
{
    use Notifiable;

    public function getTelegramChatId()
    {
        return config('telegram.bot_chat');
    }
}
