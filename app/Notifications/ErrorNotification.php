<?php

namespace BB\Notifications;

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

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($level = 'error', $title = 'Exception', $suppress = false, Throwable $e)
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

        $userString = \Auth::guest() ? "A guest" : \Auth::user()->name . "(#" .  \Auth::user()->id . ")";

        $notificationContent = $icon[$this->level] . "<b>{$this->title}</b>\n\n" .
            "Path: <b>/" . \Request::path() . "</b> \n" .
            "User: <b>" . $userString . "</b> \n" .
            "Message: <b>" . $this->e->getMessage() . "</b> \n"  .
            "File: <b>" . $this->e->getFile() . "</b> \n"  .
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
