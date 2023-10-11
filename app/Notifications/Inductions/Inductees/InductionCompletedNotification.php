<?php

namespace BB\Notifications\Inductions\Inductees;

use BB\Entities\Equipment;
use BB\Notifications\Inductions\AbstractInductionNotification;
use BB\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class InductionCompletedNotification extends AbstractInductionNotification
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $equipmentNames = $this->equipment->pluck('name')->implode(', ');
        $trainedInstructions = $this->equipment
            ->filter(function (Equipment $equipment) {
                return trim($equipment->trained_instructions);
            });

        $mailMessage = (new MailMessage)
            ->subject("You're trained on {$equipmentNames}!")
            ->line("You've been marked as trained on {$equipmentNames}.")
            ->line("This means you're free to start making things!");

        if ($trainedInstructions->count() > 0) {
            $mailMessage->line("Double check the equipment page online for any notes or instructions for using the equipment. This is where you might find access codes or software discounts, if those might be applicable to this equipment.");
        }

        $mailMessage->line('Please reach out in the forum or Telegram group chats if you have any questions or need any help organising training.');

        $mailMessage->action("Visit our forum", 'https://list.hacman.org.uk');
        $mailMessage->action("Join us on Telegram",  'https://docs.hacman.org.uk/Telegram/');

        return $mailMessage;
    }
}
