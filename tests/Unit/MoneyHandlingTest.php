<?php

namespace Tests\Unit;

use BB\Entities\Payment;
use BB\Entities\User;
use BB\Helpers\MembershipPayments;
use BB\Services\Credit;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

/**
 * Characterises the money path ahead of the PHP 7.2 -> 8.4 ladder.
 *
 * Two things make this worth pinning before the engine moves:
 *
 *  - Balances are summed from payments, both in integer pence, so this pins
 *    what's added and what's taken away.
 *
 *  - The grace-period logic compares with `==`. PHP 8 changed string-to-number
 *    comparison, so the mapping is pinned for every payment method including
 *    the unknown/empty/null fallbacks.
 */
class MoneyHandlingTest extends TestCase
{
    /**
     * @param int[] $amounts pence, as stored in payments.amount
     * @param int   $expectedPence
     */
    #[DataProvider('topUps')]
    #[Test]
    public function a_balance_recalculation_adds_up_the_top_ups(array $amounts, $expectedPence)
    {
        $user = User::factory()->create(['cash_balance' => 0]);

        foreach ($amounts as $amount) {
            $this->balancePayment($user, $amount);
        }

        $this->recalculate($user);

        $this->assertSame($expectedPence, (int) $user->fresh()->cash_balance);
    }

    /**
     * @return array<string,array{0:int[],1:int}>
     */
    public static function topUps()
    {
        return [
            'one top-up'      => [[1999], 1999],
            'several top-ups' => [[530, 1240, 7], 1777],
        ];
    }

    #[Test]
    public function spends_are_subtracted_from_the_balance()
    {
        $user = User::factory()->create(['cash_balance' => 0]);

        $this->balancePayment($user, 2000);
        $this->balanceSpend($user, 555);

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
        $user = User::factory()->create(['cash_balance' => 1999]);

        $this->assertSame('£19.99', $this->creditFor($user)->getBalanceFormatted());
    }

    #[Test]
    #[TestWith([-50, 'negative'])] // under a pound overdrawn
    #[TestWith([-1, 'negative'])]
    #[TestWith([0, 'positive'])]
    #[TestWith([50, 'positive'])]
    public function a_balance_is_negative_from_a_penny_overdrawn(int $balance, string $sign)
    {
        $user = User::factory()->create(['cash_balance' => $balance]);

        $this->assertSame($sign, $this->creditFor($user)->getBalanceSign());
    }

    private function balancePayment(User $user, int $pence)
    {
        return Payment::factory()->create([
            'user_id' => $user->id,
            'reason'  => 'balance',
            'source'  => 'gocardless',
            'status'  => 'paid',
            'amount'  => $pence,
        ]);
    }

    private function balanceSpend(User $user, int $pence)
    {
        return Payment::factory()->create([
            'user_id' => $user->id,
            'reason'  => 'equipment-fee',
            'source'  => 'balance',
            'status'  => 'paid',
            'amount'  => $pence,
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
