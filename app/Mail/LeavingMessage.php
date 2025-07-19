<?php

namespace BB\Mail;

use BB\Entities\StorageBox;
use BB\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeavingMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Membership cancellation confirmed - you\'re leaving Hackspace Manchester')
            ->replyTo('board@hacman.org.uk', 'Hackspace Manchester Board')
            ->markdown('emails.user-leaving', [
                'name' => $this->user->given_name
            ]);
    }
}
