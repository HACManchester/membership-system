<?php namespace BB\Console\Commands\Payments;

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Repo\SubscriptionChargeRepository;
use BB\Services\MemberSubscriptionCharges;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * One-off clean up for the charge backlog described in docs/billing.md.
 *
 * When a member's payment method went away - they cancelled, or their bank cancelled
 * the mandate - their outstanding charges were left behind. The nightly billing run
 * dropped them silently (no payment method to bill), so they sat at 'due' while a new
 * one was raised every month. The moment the member set up a new mandate the whole
 * backlog became billable and went out in a single run.
 *
 * Reports both halves of the damage: charges still waiting to do this, and charges
 * this already happened to. Only the first half can be fixed here - the second needs
 * refunds, which is a decision for a human.
 */
class AuditSubscriptionCharges extends Command
{

    protected $signature = 'bb:audit-sub-charges
                            {--fix : Cancel the uncollectable outstanding charges}
                            {--days= : How long after its charge date a collection counts as backdated (defaults to the billing config)}
                            {--since=2026-01-01 : Only report collections from this date onwards}';

    protected $description = 'Find subscription charges that can never be collected, and ones that were collected long after the fact';

    /**
     * @var SubscriptionChargeRepository
     */
    private $subscriptionChargeRepository;

    /**
     * @var MemberSubscriptionCharges
     */
    private $subscriptionChargeService;

    public function __construct(SubscriptionChargeRepository $subscriptionChargeRepository, MemberSubscriptionCharges $subscriptionChargeService)
    {
        parent::__construct();

        $this->subscriptionChargeRepository = $subscriptionChargeRepository;
        $this->subscriptionChargeService = $subscriptionChargeService;
    }

    public function handle()
    {
        $days = (int) ($this->option('days') ?: config('membership.billing.max_charge_age_days'));
        $since = Carbon::parse($this->option('since'))->startOfDay();

        $this->warn('created_at on subscription_charge may record the last write rather than the');
        $this->warn('creation - see docs/billing.md. This report never uses it.');
        $this->line('');

        $outstanding = $this->reportUncollectableCharges();
        $this->line('');
        $this->reportBackdatedCollections($days, $since);

        if ($this->option('fix')) {
            $this->line('');
            $this->cancelCharges($outstanding);
        } elseif ($outstanding->isNotEmpty()) {
            $this->line('');
            $this->info('Re-run with --fix to cancel the ' . $outstanding->count() . ' uncollectable charge(s).');
        }

        return 0;
    }

    /**
     * Outstanding charges for members who have no way to pay them - the backlog waiting
     * to be billed against whatever mandate the member sets up next
     *
     * @return \Illuminate\Support\Collection
     */
    private function reportUncollectableCharges()
    {
        $charges = SubscriptionCharge::with('user')
            ->whereIn('status', ['pending', 'due'])
            ->orderBy('user_id')
            ->orderBy('charge_date')
            ->get()
            ->filter(function (SubscriptionCharge $charge) {
                // Exactly what the nightly run now refuses to touch
                return ! $this->subscriptionChargeService->canBeBilled($charge->user);
            })
            ->values();

        // No date cutoff here: however old, these are still waiting to be billed the
        // moment the member sets up a mandate, so they all want clearing out
        $this->info('Uncollectable outstanding charges: ' . $charges->count());

        if ($charges->isEmpty()) {
            return $charges;
        }

        $this->table(
            ['Charge', 'Member', 'Member status', 'Payment method', 'Charge date', 'Status', 'Amount'],
            $charges->map(function (SubscriptionCharge $charge) {
                return [
                    $charge->id,
                    $this->memberName($charge),
                    $charge->user ? $charge->user->status : '-',
                    $charge->user && $charge->user->payment_method ? $charge->user->payment_method : 'none',
                    $charge->charge_date->format('Y-m-d'),
                    $charge->status,
                    $charge->amount,
                ];
            })->all()
        );

        $this->summariseByMember($charges->groupBy('user_id'), 'waiting to be billed');

        return $charges;
    }

    /**
     * Charges where the money was taken long after the charge date - the members who
     * have already been hit by this and may be owed a refund
     *
     * @param integer $days
     * @param Carbon $since
     */
    private function reportBackdatedCollections($days, Carbon $since)
    {
        $collections = collect();

        Payment::where('reason', 'subscription')
            ->whereNotNull('reference')
            ->where('reference', '!=', '')
            ->whereIn('status', ['paid', 'pending', 'pending_submission', 'processing'])
            ->orderBy('id')
            ->chunk(500, function ($payments) use (&$collections, $days) {
                $charges = SubscriptionCharge::with('user')
                    ->whereIn('id', $payments->pluck('reference')->all())
                    ->get()
                    ->keyBy('id');

                foreach ($payments as $payment) {
                    $charge = $charges->get((int) $payment->reference);
                    if ( ! $charge) {
                        continue;
                    }

                    // paid_at is written once when the money moves; created_at is only a
                    // fallback for payments still in flight
                    $collectedAt = $payment->paid_at ?: $payment->created_at;
                    if ( ! $collectedAt) {
                        continue;
                    }

                    if ($charge->charge_date->lt($collectedAt->copy()->subDays($days))) {
                        $collections->push(compact('charge', 'payment', 'collectedAt'));
                    }
                }
            });

        // Filtered on when the money moved, not the charge date: an ancient charge
        // collected this year is exactly what we're looking for
        [$collections, $olderThanCutoff] = $collections->partition(function ($row) use ($since) {
            return $row['collectedAt']->gte($since);
        });

        $this->info('Charges collected more than ' . $days . ' days after their charge date, since ' . $since->format('Y-m-d') . ': ' . $collections->count());
        $this->reportSkipped($olderThanCutoff, $since);

        if ($collections->isEmpty()) {
            return;
        }

        $this->table(
            ['Charge', 'Member', 'Charge date', 'Collected', 'Days late', 'Amount', 'GoCardless payment'],
            $collections->map(function ($row) {
                return [
                    $row['charge']->id,
                    $this->memberName($row['charge']),
                    $row['charge']->charge_date->format('Y-m-d'),
                    $row['collectedAt']->format('Y-m-d'),
                    $row['charge']->charge_date->diffInDays($row['collectedAt']),
                    $row['payment']->amount,
                    $row['payment']->source_id ?: '-',
                ];
            })->all()
        );

        $this->summariseByMember(
            $collections->groupBy(function ($row) {
                return $row['charge']->user_id;
            }),
            'already collected',
            function ($row) {
                return $row['payment']->amount;
            }
        );

        $this->warn('These were already collected. Refunds have to be issued at GoCardless - this command will not touch them.');
    }

    /**
     * Say what the cutoff hid, so a quiet report is never mistaken for a clean one
     *
     * @param \Illuminate\Support\Collection $skipped
     * @param Carbon $since
     */
    private function reportSkipped($skipped, Carbon $since)
    {
        if ($skipped->isEmpty()) {
            return;
        }

        $this->line('  (' . $skipped->count() . ' older than ' . $since->format('Y-m-d') . ' not shown)');
    }

    /**
     * @param \Illuminate\Support\Collection $charges
     */
    private function cancelCharges($charges)
    {
        if ($charges->isEmpty()) {
            $this->info('Nothing to fix.');
            return;
        }

        foreach ($charges as $charge) {
            $this->subscriptionChargeRepository->cancelCharge($charge->id);
        }

        $message = 'bb:audit-sub-charges cancelled ' . $charges->count() . ' uncollectable subscription charges';
        Log::warning($message . ': ' . $charges->pluck('id')->implode(', '));
        $this->info($message . '.');
    }

    /**
     * @param \Illuminate\Support\Collection $groups Rows keyed by user id
     * @param string $label
     * @param callable|null $amount How to read the amount from a row
     */
    private function summariseByMember($groups, $label, callable $amount = null)
    {
        $this->line('');
        $this->line('By member (' . $label . '):');

        foreach ($groups as $userId => $rows) {
            $total = $amount ? $rows->sum($amount) : $rows->sum('amount');
            $name = $amount ? $this->memberName($rows->first()['charge']) : $this->memberName($rows->first());

            $this->line('  ' . $name . ' (user ' . $userId . '): ' . $rows->count() . ' x charge, total ' . $total);
        }
    }

    /**
     * @param SubscriptionCharge $charge
     * @return string
     */
    private function memberName(SubscriptionCharge $charge)
    {
        return $charge->user ? $charge->user->name : '[deleted user]';
    }

}
