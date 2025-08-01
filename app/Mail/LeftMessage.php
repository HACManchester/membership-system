<?php

namespace BB\Mail;

use BB\Entities\StorageBox;
use BB\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeftMessage extends Mailable implements ShouldQueue
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
            ->subject('Your Hackspace Manchester membership has ended')
            ->replyTo('board@hacman.org.uk', 'Hackspace Manchester Board')
            ->markdown('emails.user-left', [
                'name' => $this->user->given_name
            ]);
    }
}
