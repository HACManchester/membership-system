<?php

namespace BB\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use BB\Helpers\TelegramHelper;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CheckMembershipStatus::class,
        Commands\CreateTodaysSubCharges::class,
        Commands\BillMembers::class,
        Commands\Payments\CheckForPossibleDuplicates::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $telegram = new TelegramHelper("createSubscriptionCharges");

        $schedule
            ->command(Commands\CheckMembershipStatus::class)
            ->dailyAt('06:00')
            ->then(function () use ($telegram) {
                $message = "✔️ Checked Memberships";
                \Log::info($message);
                $telegram->notify(
                    TelegramHelper::JOB,
                    $message
                );
            });

        $schedule
            ->command(Commands\CreateTodaysSubCharges::class)
            ->dailyAt('01:00')
            ->then(function () use ($telegram) {
                $message = "✔️ Created today's subscription charges";
                \Log::info($message);
                $telegram->notify(
                    TelegramHelper::JOB,
                    $message
                );
            });

        $schedule
            ->command(Commands\BillMembers::class)
            ->dailyAt('01:30')
            ->then(function () use ($telegram) {
                $message = "✅ Billed members.";
                \Log::info($message);
                $telegram->notify(
                    TelegramHelper::JOB,
                    $message
                );
            });

        $schedule
            ->command(Commands\Payments\CheckForPossibleDuplicates::class)
            ->dailyAt('07:00')->timezone('Europe/London')
            ->description('Possible duplicate payments') // this becomes email subject
            ->emailOutputTo('board@hacman.org.uk', true);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
