<?php

namespace BB\Listeners;

use BB\Events\NewExpenseSubmitted;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailboardAboutExpense implements ShouldQueue
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * Create the event listener.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  NewExpenseSubmitted  $event
     * @return void
     */
    public function handle(NewExpenseSubmitted $event)
    {
        $this->mailer->send('emails.new-expense', ['user' => $event->expense->user, 'expense' => $event->expense], function ($m) {
            $m->to('board@hacman.org.uk', 'board')->subject('New expense submitted');
        });
    }
}
