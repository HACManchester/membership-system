<?php

namespace BB\Console\Commands;

use BB\Helpers\TelegramHelper;
use Illuminate\Console\Command;

class TestScheduledTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:scheduled-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing scheduled tasks on server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegram = new TelegramHelper("Test Scheduled Task");

        $telegram->notify(
            TelegramHelper::JOB,
            "✔️ Test Scheduled Task successfully ran (notification from within task)"
        );
    }
}
