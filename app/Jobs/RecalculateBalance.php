<?php

namespace BB\Jobs;

use BB\Entities\User;
use BB\Services\Credit;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RecalculateBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(Credit $bbCredit)
    {
        $oldBalance = $this->user->cash_balance;

        $bbCredit->setUserId($this->user->id);
        $bbCredit->recalculate();        

        $newBlalance = $this->user->fresh()->cash_balance;
        \Log::debug("Recalculated balance for user {$this->user->id}. Balance changed from {$oldBalance} to {$newBlalance}");
    }
}
