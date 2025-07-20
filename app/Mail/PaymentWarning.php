<?php

namespace BB\Mail;

use BB\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentWarning extends Mailable implements ShouldQueue
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
            ->subject('Payment issue - Action required for your Hackspace Membership')
            ->replyTo('board@hacman.org.uk', 'Hackspace Manchester Board')
            ->markdown('emails.payment-warning', [
                'name' => $this->user->given_name
            ]);
    }
}
