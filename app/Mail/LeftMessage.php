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
    public $memberBox;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, StorageBox $storageBox = null)
    {
        $this->user = $user;
        $this->memberBox = $storageBox;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('You have left Hackspace Manchester')
            ->view('emails.user-left');
    }
}
