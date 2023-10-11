<?php

namespace BB\Notifications\Inductions\Inductees;

use BB\Entities\Equipment;
use BB\Notifications\Inductions\AbstractInductionNotification;
use BB\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class InductionRequestedNotification extends AbstractInductionNotification
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
        $inductionInstructions = $this->equipment
            ->unique('induction_instructions')
            ->filter(function (Equipment $equipment) {
                return trim($equipment->induction_instructions);
            });

        $mailMessage = (new MailMessage)
            ->subject("You've requested training on {$equipmentNames}")
            ->line("You've requested training on {$equipmentNames}.");

        if ($inductionInstructions->count() > 0) {
            $mailMessage->line('Training is organised and delivered in different ways for different pieces of equipment.');
            $mailMessage->line('We have the following instructions for the pieces of equipment covered by your training request:');

            foreach ($inductionInstructions as $equipment) {
                $mailMessage->line("- {$equipment->name}: {$equipment->induction_instructions}");
            }
        }
        $mailMessage->line('Please reach out in the forum or Telegram group chats if you have any questions or need any help organising training.');

        $mailMessage->action("Visit our forum", 'https://list.hacman.org.uk');
        $mailMessage->action("Join us on Telegram",  'https://docs.hacman.org.uk/Telegram/');

        return $mailMessage;
    }
}
