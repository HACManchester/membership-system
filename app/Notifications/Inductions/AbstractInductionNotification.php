<?php

namespace BB\Notifications\Inductions;

use BB\Entities\Induction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

abstract class AbstractInductionNotification extends Notification
{
    use Queueable;

    /** @var Induction */
    protected $induction;

    /** @var Collection */
    protected $equipment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Induction $induction, Collection $equipment)
    {
        $this->induction = $induction;
        $this->equipment = $equipment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    abstract public function toMail($notifiable);

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'induction_id' => $this->induction->id,
        ];
    }
}
