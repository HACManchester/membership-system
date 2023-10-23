<?php namespace BB\Console;

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

        // $schedule->command('bb:fix-equipment-log')->hourly()
        //     ->then( function () { $this->notifyTelegram(''); } );

        // $schedule->command('bb:calculate-equipment-fees')->dailyAt('02:00')
        //     ->then( function () { $this->notifyTelegram(''); } );

        // todo: These are currently running via cron, via automate.sh. We should move back to using the Laravel scheduler?

        $schedule
            ->command('bb:check-memberships')
            ->dailyAt('06:00')
            ->then( function () use ($telegram) {
                $message = "✔️ Checked Memberships";
                \Log::info($message); 
                $telegram->notify(
                    TelegramHelper::JOB, 
                    $message
                );
            });

        $schedule
            ->command('bb:update-balances')
            ->dailyAt('03:00')
            ->then( function () use ($telegram) {
                $message = "✔️ Updated Balances";
                \Log::info($message); 
                $telegram->notify(
                    TelegramHelper::JOB, 
                    $message
                );
            });

        $schedule
            ->command('bb:create-todays-sub-charges')
            ->dailyAt('01:00')
            ->then( function () use ($telegram) { 
                $message = "✔️ Created today's subscription charges";
                \Log::info($message); 
                $telegram->notify(
                    TelegramHelper::JOB, 
                    $message
                );
            } );

        $schedule
            ->command('bb:bill-members')
            ->dailyAt('01:30')
            ->then( function ($result) use ($telegram) { 
                $message = "✅ Billed members: " . $result['gc_users'] . " GC users, " . $result['gc_users_blled'] . " bills created.";
                \Log::info($message); 
                $telegram->notify(
                    TelegramHelper::JOB, 
                    $message
                );
            });

        $schedule
            ->command('device:check-online')
            ->everyTenMinutes()
            ->then( function () { });
    }
}
