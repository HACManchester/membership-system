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
        Commands\CalculateProposalVotes::class,
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

        // $schedule->command('bb:calculate-proposal-votes')->hourly()
        //     ->then( function () { $this->notifyTelegram(''); } );

        // $schedule->command('bb:fix-equipment-log')->hourly()
        //     ->then( function () { $this->notifyTelegram(''); } );

        // $schedule->command('bb:calculate-equipment-fees')->dailyAt('02:00')
        //     ->then( function () { $this->notifyTelegram(''); } );

        $schedule
            ->command('bb:check-memberships')
            ->dailyAt('06:00')
            ->then( function () { 
                $telegram->notify(
                    TelegramHelper::JOB, 
                    "✔️ Checked Memberships"
                );
            });

        $schedule
            ->command('bb:update-balances')
            ->dailyAt('03:00')
            ->then( function () {
                $telegram->notify(
                    TelegramHelper::JOB, 
                    "✔️ Updated Balances"
                ); 
            });

        $schedule
            ->command('bb:create-todays-sub-charges')
            ->dailyAt('01:00')
            ->then( function () { 
                $telegram->notify(
                    TelegramHelper::JOB, 
                    "✔️ Created today's subscription charges"
                );
            } );

        $schedule
            ->command('bb:bill-members')
            ->dailyAt('01:30')
            ->then( function ($result) { 
                $notification = "✅ Billed members: " . $result['gc_users'] . " GC users, " . $result['gc_users_blled'] . " bills created.";
                $telegram->notify(
                    TelegramHelper::JOB, 
                    $notification
                );
            });

        $schedule
            ->command('device:check-online')
            ->everyTenMinutes()
            ->then( function () { });
    }
}
