<?php

namespace BB\Notifications\TrainingRecords;

use BB\Entities\TrainingRecord;
use BB\Entities\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

abstract class AbstractTrainingRecordNotification extends Notification
{
    use Queueable;

    /** @var TrainingRecord */
    protected $trainingRecord;

    /** @var Collection */
    protected $equipment;

    /** @var Course|null */
    protected $course;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(TrainingRecord $trainingRecord, Collection $equipment)
    {
        $this->trainingRecord = $trainingRecord;
        $this->equipment = $equipment;
        $this->course = $trainingRecord->course;
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
            'induction_id' => $this->trainingRecord->id,
        ];
    }
}
