<?php

namespace BB\Listeners;

use BB\Events\NewExpenseSubmitted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailboardAboutExpense
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewExpenseSubmitted  $event
     * @return void
     */
    public function handle(NewExpenseSubmitted $event)
    {
        //
    }
}
