<?php

namespace BB\Notifications\Inductions\Inductees;

use BB\Notifications\Inductions\AbstractInductionNotification;
use BB\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class InductionMarkedAsTrainerNotification extends AbstractInductionNotification
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Use course-based messaging when we have a course and it's live
        if ($this->course && $this->course->live) {
            $mailMessage = (new MailMessage)
                ->subject("You're a trainer for {$this->course->name}!")
                ->line("You can now start training others for the {$this->course->name} course!")
                ->line("Visit the course training page to manage training requests and contact those awaiting training.");

            $mailMessage->action("View {$this->course->name} Training", route('courses.training.index', $this->course));
        } else {
            // Fall back to equipment-based messaging
            $equipmentNames = $this->equipment->pluck('name')->implode(', ');

            $mailMessage = (new MailMessage)
                ->subject("You're a trainer on {$equipmentNames}!")
                ->line("You can now start training others on {$equipmentNames}!")
                ->line("Visit the equipment page to manage training, and contact those awaiting training");

            foreach ($this->equipment as $equipment) {
                $mailMessage->action("View {$equipment->name} page",  route('equipment.show', $equipment->slug));
            }
        }

        return $mailMessage;
    }
}
