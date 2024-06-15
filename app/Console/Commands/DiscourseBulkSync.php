<?php

namespace BB\Console\Commands;

use BB\Entities\User;
use BB\Jobs\DiscourseSync as DiscourseSyncJob;
use Illuminate\Console\Command;

class DiscourseBulkSync extends Command
{
    protected $signature = 'discourse:bulk-sync {--apply}';

    protected $description = 'Sync members to Discourse who are currently active, or became inactive within the last 6 months';

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
                DiscourseSyncJob::dispatch($user);
                $this->info("Queued a job to sync {$user->name} ({$user->id}) with Discourse.");
            } else {
                $this->info("Would have queued a job to sync {$user->name} ({$user->id}) with Discourse.");
            }
        }
    }
}
