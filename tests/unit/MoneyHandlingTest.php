<?php

namespace Tests\Unit;

use BB\Entities\Payment;
use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Services\Credit;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Characterises the money path ahead of the PHP 7.2 -> 8.4 ladder.
 *
 * Two things make this worth pinning before the engine moves:
 *
 *  - Units are mixed. `payments.amount` is a DOUBLE holding pounds, while
 *    `users.cash_balance` is integer pence, so every balance recalculation runs
 *    a float through a *100 and lands it in an integer column. Several of the
 *    amounts members actually pay are not exactly representable as floats
 *    (19.99 * 100 is 1998.9999999999998), so this asserts the round-trip still
 *    produces the right pence. PHP 8.1 also began deprecating implicit
 *    float-to-int conversions that lose precision.
 *
 *  - The grace-period logic compares with `==`. PHP 8 changed string-to-number
 *    comparison, so the mapping is pinned for every payment method including
 *    the unknown/empty/null fallbacks.
 */
class MoneyHandlingTest extends TestCase
{
    /**
     * @param float[] $amounts pounds, as stored in payments.amount
     * @param int     $expectedPence
     */
    #[DataProvider('awkwardAmounts')]
    #[Test]
    public function a_balance_recalculation_lands_on_exact_pence(array $amounts, $expectedPence)
    {
        $user = factory(User::class)->create(['cash_balance' => 0]);

        foreach ($amounts as $amount) {
            $this->balancePayment($user, $amount);
        }

        $this->recalculate($user);

        $this->assertSame($expectedPence, (int) $user->fresh()->cash_balance);
    }

    /**
     * Each case is a sum whose float representation is not exact.
     *
     * @return array<string,array{0:float[],1:int}>
     */
    public static function awkwardAmounts()
    {
        return [
            'a single exact amount'          => [[17.70], 1770],
            'the classic 0.1 + 0.2'          => [[0.10, 0.20], 30],
            'two amounts summing past .999'  => [[5.30, 12.40], 1770],
            'a price just under the pound'   => [[19.99], 1999],
            'a few pence'                    => [[0.07], 7],
            'the recommended subscription'   => [[22.00], 2200],
        ];
    }

    #[Test]
    public function spends_are_subtracted_from_the_balance()
    {
        $user = factory(User::class)->create(['cash_balance' => 0]);

        $this->balancePayment($user, 20.00);
        $this->balanceSpend($user, 5.55);

        $this->recalculate($user);

        $this->assertSame(1445, (int) $user->fresh()->cash_balance);
    }

    /**
     * @param string|null $paymentMethod
     * @param string      $expectedCutoff date the grace period reaches back to
     */
    #[DataProvider('gracePeriods')]
    #[Test]
    public function each_payment_method_keeps_its_grace_period($paymentMethod, $expectedCutoff)
    {
        $reference = Carbon::parse('2026-06-15 13:45:00');

        $cutoff = MembershipPayments::getSubGracePeriodDate($paymentMethod, $reference->copy());

        $this->assertSame($expectedCutoff, $cutoff->toDateString());
        $this->assertSame('00:00:00', $cutoff->toTimeString(), 'the cutoff must be midnight-aligned');
    }

    /**
     * The `else` branch is the safety net for anything unrecognised, so the
     * null/empty/unknown cases matter as much as the named ones — PHP 8 changed
     * how `==` compares a non-numeric string to other types.
     *
     * @return array<string,array{0:string|null,1:string}>
     */
    public static function gracePeriods()
    {
        // Reference date is 2026-06-15.
        return [
            'gocardless'          => ['gocardless', '2026-06-01'],          // -14 days
            'gocardless-variable' => ['gocardless-variable', '2026-06-05'], // -10 days
            'standing-order'      => ['standing-order', '2025-12-15'],      // -6 months
            'cash'                => ['cash', '2026-06-08'],                // -7 days
            'an unknown method'   => ['something-else', '2026-06-08'],
            'an empty string'     => ['', '2026-06-08'],
            'null'                => [null, '2026-06-08'],
        ];
    }

    #[Test]
    public function a_balance_is_reported_in_pounds()
    {
        $user = factory(User::class)->create(['cash_balance' => 1999]);
        $credit = $this->creditFor($user);

        $this->assertSame(19.99, $credit->getBalance());
        $this->assertSame('£19.99', $credit->getBalanceFormatted());
    }

    /**
     * Pinning a known wart rather than endorsing it: getBalanceSign() casts the
     * *pounds* value to int before comparing, so anything owing less than a
     * whole pound truncates to 0 and reports as positive. BalanceController is
     * the only consumer. Left as-is deliberately — changing it changes what
     * members see, which is a call for the board, not a side effect of an
     * upgrade. Flagged so the upgrade doesn't silently alter it either way.
     */
    #[Test]
    public function a_sub_one_pound_debt_currently_reports_as_positive()
    {
        $inDebtByFiftyPence = factory(User::class)->create(['cash_balance' => -50]);
        $inDebtByAPound = factory(User::class)->create(['cash_balance' => -100]);

        $this->assertSame('positive', $this->creditFor($inDebtByFiftyPence)->getBalanceSign());
        $this->assertSame('negative', $this->creditFor($inDebtByAPound)->getBalanceSign());
    }

    private function balancePayment(User $user, $pounds)
    {
        return Payment::factory()->create([
            'user_id' => $user->id,
            'reason'  => 'balance',
            'source'  => 'gocardless',
            'status'  => 'paid',
            'amount'  => $pounds,
        ]);
    }

    private function balanceSpend(User $user, $pounds)
    {
        return Payment::factory()->create([
            'user_id' => $user->id,
            'reason'  => 'equipment-fee',
            'source'  => 'balance',
            'status'  => 'paid',
            'amount'  => $pounds,
        ]);
    }

    private function recalculate(User $user)
    {
        $credit = $this->creditFor($user);
        $credit->recalculate();
    }

    private function creditFor(User $user)
    {
        $credit = app(Credit::class);
        $credit->setUserId($user->id);

        return $credit;
    }
}
