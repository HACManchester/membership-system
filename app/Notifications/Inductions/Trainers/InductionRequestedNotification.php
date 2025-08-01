<?php

namespace BB\Notifications\Inductions\Trainers;

use BB\Entities\Equipment;
use BB\Entities\User;
use BB\Notifications\Inductions\AbstractInductionNotification;
use BB\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Html\HtmlFacade;
use Illuminate\Support\HtmlString;

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
        $inductee = $this->induction->user;

        // Use course-based messaging when courses are live and we have a course
        if (\BB\Entities\Course::isLive() && $this->course) {
            $mailMessage = (new MailMessage)
                ->subject("{$inductee->name} has requested training for {$this->course->name}")
                ->line("{$inductee->name} has requested training for the {$this->course->name} course.");

            if (trim($this->course->training_organisation_description ?? '')) {
                $mailMessage->line("Training details: {$this->course->training_organisation_description}");
            }

            $mailMessage->line('You can view and manage training requests from the course training page.');
            $mailMessage->action("View {$this->course->name} Training", route('courses.training.index', $this->course));
        } else {
            // Fall back to equipment-based messaging
            $equipmentNames = $this->equipment->pluck('name')->implode(', ');
            $inductionInstructions = $this->equipment
                ->unique('induction_instructions')
                ->filter(function (Equipment $equipment) {
                    return trim($equipment->induction_instructions);
                });

            $mailMessage = (new MailMessage)
                ->subject("{$inductee->name} has requested training on {$equipmentNames}")
                ->line("{$inductee->name} has requested training on {$equipmentNames}.");

            if ($inductionInstructions->count() > 0) {
                $mailMessage->line('They have been shown & emailed the induction instructions for each piece of equipment:');

                foreach ($inductionInstructions as $equipment) {
                    $mailMessage->line("- {$equipment->name}: {$equipment->induction_instructions}");
                }
            } else {
                $mailMessage->line('There are no induction instructions for these pieces of equipment, so the member has not been advised on how to arrange training.');
            }

            $mailMessage->line('You can email members awaiting training from equipment pages.');

            foreach ($this->equipment as $equipment) {
                $mailMessage->action("View {$equipment->name} page",  route('equipment.show', $equipment->slug));
            }
        }

        return $mailMessage;
    }
}
