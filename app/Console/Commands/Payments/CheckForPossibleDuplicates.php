<?php

namespace BB\Console\Commands\Payments;

use BB\Entities\Payment;
use BB\Repo\PaymentRepository;
use Illuminate\Console\Command;

class CheckForPossibleDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-for-possible-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Users can accidentally queue up duplicate payments, this command will check for possible duplicates and alert the board to review them.';

    /** @var PaymentRepository */
    protected $paymentRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentRepository $paymentRepository)
    {
        parent::__construct();

        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $possibleDuplicates = $this->paymentRepository->getPossibleDuplicates();
        if ($possibleDuplicates->isEmpty()) {
            return;
        }

        $this->output->title('Possible Duplicate Payments');
        $this->output->text('The following users have multiple payments pending up with the same reason and amount.');
        $this->output->table(
            ['User', 'Reason', 'Amount', 'Count'],
            $possibleDuplicates->map(function ($payment) {
                return [
                    $payment->user->name,
                    $payment->reason,
                    $payment->amount,
                    $payment->count
                ];
            })->toArray()
        );

        $this->output->text('Please review these payments and ensure they are not duplicates.');
        $this->output->text(route('payments.possible-duplicates'));
    }
}
