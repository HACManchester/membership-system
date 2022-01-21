<?php namespace BB\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use BB\Helpers\TelegramHelper;

class BillMembers extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'bb:bill-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bill members based on the sub charge records';

    /**
     * @var \BB\Services\MemberSubscriptionCharges
     */
    private $subscriptionChargeService;

    private $telegramHelper;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->subscriptionChargeService = \App::make('\BB\Services\MemberSubscriptionCharges');
        $this->telegramHelper = new TelegramHelper("billMembers");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            //Update the payments status from pending to due
            $this->telegramHelper->notify(
                TelegramHelper::JOB, 
                "Start billing..."
            );

            $this->info('Moving sub charges to due');
            $this->subscriptionChargeService->makeChargesDue();
            $this->telegramHelper->notify(
                TelegramHelper::JOB, 
                "Moved sub charges to due"
            );

            
            //Bill the due charges
            $this->info('Billing members');
            $this->subscriptionChargeService->billMembers();
            $this->telegramHelper->notify(
                TelegramHelper::JOB, 
                "Billed members - job ran."
            );

            $this->info('Finished');

        }catch(Exception $e){
            $this->telegramHelper->notify(
                TelegramHelper::ERROR, 
                "billMembers encountered an exception"
            );

            \Log::error($e);
        }
    }
}
