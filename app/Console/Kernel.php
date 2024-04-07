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
        Commands\RecalculateUserBalances::class,
        Commands\CheckFixEquipmentLog::class,
        Commands\CalculateEquipmentFees::class,
        Commands\CreateTodaysSubCharges::class,
        Commands\BillMembers::class,
        Commands\CheckDeviceOnlineStatuses::class,
        Commands\TestScheduledTask::class,
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
            ->then(function ($result) use ($telegram) {
                $message = "✅ Billed members: " . $result['gc_users'] . " GC users, " . $result['gc_users_blled'] . " bills created.";
                \Log::info($message);
                $telegram->notify(
                    TelegramHelper::JOB,
                    $message
                );
            });

        $schedule
            ->command(Commands\TestScheduledTask::class)
            ->hourlyAt(37)
            ->then(function ($result) use ($telegram) {
                $message = "✔️ Test Scheduled Task successfully ran (notification from 'then' hook)";
                $telegram->notify(
                    TelegramHelper::JOB,
                    $message
                );
            });

        $schedule
            ->command(Commands\Payments\CheckForPossibleDuplicates::class)
            ->dailyAt('14:37')
            ->emailOutputTo('board@hacman.org.uk', true);
    }
}
