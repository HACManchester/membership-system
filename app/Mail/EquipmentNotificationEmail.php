<?php

namespace BB\Mail;

use BB\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EquipmentNotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $subjectLine;
    public $messageBody;
    public $equipment_name;
    public $training_status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $subjectLine, $messageBody, $equipment_name, $training_status)
    {
        $this->user = $user;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
        $this->equipment_name = $equipment_name;
        $this->training_status = $training_status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->subjectLine)
            ->replyTo('info@hacman.org.uk', 'Hackspace Manchester Info')
            ->view('emails.equipment-notification');
    }
}
