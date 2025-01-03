<?php

namespace BB\Console\Commands;

use BB\Entities\User;
use BB\Jobs\RecalculateBalance as RecalculateBalanceJob;
use Illuminate\Console\Command;

class RecalculateAllBalances extends Command
{
    protected $signature = 'payments:recalculate-all-balances {--apply}';

    protected $description = 'Recalculates the cash_balance for all users.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::where(function ($query) {
            $query->active();
        })
            ->orWhere(function ($query) {
                $query->recentlyLapsed();
            })
            ->get();

        foreach ($users as $user) {
            if ($this->option('apply')) {
                RecalculateBalanceJob::dispatch($user);
                $this->info("Queued a job to recalculate the cash_balance of {$user->name} ({$user->id}).");
            } else {
                $this->info("Would have queued a job to recalculate the cash_balance of {$user->name} ({$user->id}).");
            }
        }
    }
}
