<?php namespace BB\Console\Commands;

use BB\Helpers\TelegramHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateTodaysSubCharges extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'bb:create-todays-sub-charges {dayOffset=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all the subscription changes for today';

    /**
     * @var \BB\Services\MemberSubscriptionCharges
     */
    private $subscriptionChargeService;

    /**
     * @var TelegramHelper
     */
    private $telegramHelper;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->subscriptionChargeService = \App::make('\BB\Services\MemberSubscriptionCharges');
        $this->telegramHelper = new TelegramHelper("createSubscriptionCharges");
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dayOffset = intval($this->argument('dayOffset'));

        $failed = [];

        // As well as the target day, re-run every day between it and today so a
        // missed or failed run self-heals; chargeExists() makes repeat runs a no-op.
        // A negative offset (a manual backfill) walks the past days the same way.
        for ($offset = max(0, $dayOffset); $offset >= min(0, $dayOffset); $offset--) {
            $targetDate = Carbon::now()->addDays($offset);

            $this->info("Generating charges for " . $targetDate);
            $result = $this->subscriptionChargeService->createSubscriptionCharges($targetDate);

            if ($offset < $dayOffset && count($result['created']) > 0) {
                $message = "Created " . count($result['created']) . " catch-up sub charges for " . $targetDate->format('Y-m-d') . " - a previous run was missed or failed";
                Log::warning($message);
                $this->telegramHelper->notify(TelegramHelper::WARNING, $message);
            }

            $failed = array_merge($failed, $result['failed']);
        }

        $message = "Charges ran for " . Carbon::now()->addDays($dayOffset)->format('Y-m-d');
        Log::info($message);
        $this->telegramHelper->notify(TelegramHelper::JOB, $message);

        if (count($failed) > 0) {
            $message = "Could not create sub charges for: " . implode(', ', array_unique($failed));
            Log::error($message);
            $this->telegramHelper->notify(TelegramHelper::ERROR, $message);

            return 1;
        }

        return 0;
    }
}
