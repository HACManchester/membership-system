<?php

namespace BB\Console\Commands;

use BB\Entities\User;
use BB\Jobs\DiscourseSync as DiscourseSyncJob;
use Illuminate\Console\Command;

class DiscourseSync extends Command
{
    protected $signature = 'discourse:sync {user}';

    protected $description = 'Force a discourse sync for a particular user.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::find($this->argument('user'));

        DiscourseSyncJob::dispatch($user);
        $this->info("Queued a job to sync {$user} with Discourse.");
    }
}
