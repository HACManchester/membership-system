<?php

namespace BB\Mail;

use BB\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $subjectLine;
    public $messageBody;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $subjectLine, $messageBody)
    {
        $this->user = $user;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
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
            ->replyTo('board@hacman.org.uk', 'Hackspace Manchester Board')
            ->view('emails.notification');
    }
}
