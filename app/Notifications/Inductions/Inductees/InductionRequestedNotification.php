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
        // Use course-based messaging when we have a course and it's live
        if ($this->course && $this->course->live) {
            $mailMessage = (new MailMessage)
                ->subject("You've requested training for {$this->course->name}")
                ->line("You've requested training for the {$this->course->name} course.");

            if (trim($this->course->description ?? '')) {
                $mailMessage->line('Course information:');
                $mailMessage->line($this->course->description);
            }

            if (trim($this->course->training_organisation_description ?? '')) {
                $mailMessage->line('How training is organised:');
                $mailMessage->line($this->course->training_organisation_description);
            }
        } else {
            // Fall back to equipment-based messaging
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
        }
        
        $mailMessage->line('Please reach out in the forum or Telegram group chats if you have any questions or need any help organising training.');
        $mailMessage->action("Visit our forum", 'https://list.hacman.org.uk');
        $mailMessage->action("Join us on Telegram",  'https://docs.hacman.org.uk/getting_started/communications/telegram/');

        return $mailMessage;
    }
}
