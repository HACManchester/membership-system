<?php namespace BB\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client as HttpClient;

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

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->subscriptionChargeService = \App::make('\BB\Services\MemberSubscriptionCharges');
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
            $this->notifyTelegram("✔️ start billing");

            $this->info('Moving sub charges to due');
            $this->subscriptionChargeService->makeChargesDue();
            $this->notifyTelegram("✔️ moved sub charges to due");
            
            //Bill the due charges
            $this->info('Billing members');
            $this->subscriptionChargeService->billMembers();
            $this->notifyTelegram("✔️ billMembers ran");

            $this->info('Finished');

        }catch(Exception $e){
            $this->notifyTelegram("🚨 billMembers encountered an exception");
            \Log::error($e);
        }
    }

    protected function notifyTelegram($notification)
    {
        (new HttpClient)->get(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_KEY') . "/sendMessage" .
            "?chat_id=" . env('TELEGRAM_BOT_CHAT') . 
            "&text=⏲️" . urlencode($notification)
        );
    }

}
