<?php

namespace BB\Notifications;

use BB\Support\CurrentUser;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class ErrorNotification extends Notification
{
    use Queueable;

    /** @var string */
    protected $level;

    /** @var string */
    protected $title;

    /** @var bool */
    protected $suppress;

    /** @var Throwable */
    protected $e;

    public function __construct($level, $title, $suppress, Throwable $e)
    {
        $this->level = $level;
        $this->title = $title;
        $this->suppress = $suppress;
        $this->e = $e;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (!config('services.telegram-bot-api.enabled')) {
            return [];
        }

        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {

        $icon = array(
            'error' => '🛑',
            'warn' => '⚠️',
            'info' => 'ℹ️'
        );

        $user = CurrentUser::find();
        $userString = $user ? $user->name . "(#" . $user->id . ")" : "A guest";

        // Sent with parse_mode HTML: an unescaped `<` or `&` in any of these (the
        // path and member names are user-controlled) makes Telegram reject the alert
        $html = function ($value) {
            return htmlspecialchars((string) $value, ENT_NOQUOTES, 'UTF-8');
        };

        $notificationContent = $icon[$this->level] . "<b>" . $html($this->title) . "</b>\n\n" .
            "Path: <b>/" . $html(\Request::path()) . "</b> \n" .
            "User: <b>" . $html($userString) . "</b> \n" .
            "Message: <b>" . $html($this->e->getMessage()) . "</b> \n"  .
            "File: <b>" . $html($this->e->getFile()) . "</b> \n"  .
            "Line: <b>" . $this->e->getLine() . "</b>";

        $logsUrl = route('logs');

        return TelegramMessage::create()
            ->to($notifiable->getTelegramChatId())
            ->content($notificationContent)
            ->button('View logs', $logsUrl)
            ->options(['parse_mode' => 'HTML']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
