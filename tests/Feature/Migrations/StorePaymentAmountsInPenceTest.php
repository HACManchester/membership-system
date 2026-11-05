<?php

namespace Tests\Feature\Migrations;

use BB\Entities\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class StorePaymentAmountsInPenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Back to the doubles of pounds the migration starts from
        $this->migration()->down();
    }

    #[Test]
    public function migrating_converts_payment_amounts_from_pounds_to_pence()
    {
        $payment = $this->paymentOf(19.99, 0.35, 19.64); // 19.99 * 100 is 1998.9999999999998
        $whole = $this->paymentOf(22, 0, 22);
        $nothing = $this->paymentOf(0, 0, 0);

        $this->migration()->up();

        $this->assertSame([1999, 35, 1964], $this->amounts($payment));
        $this->assertSame([2200, 0, 2200], $this->amounts($whole));
        $this->assertSame([0, 0, 0], $this->amounts($nothing));
    }

    #[Test]
    #[TestWith([1000.01, 0, 1000.01])] // over £1000, e.g. already in pence
    #[TestWith([-1, 0, -1])]
    public function migrating_changes_nothing_when_an_amount_is_out_of_range(float $amount, float $fee, float $amountMinusFee)
    {
        $fine = $this->paymentOf(19.99, 0, 19.99);
        $odd = $this->paymentOf($amount, $fee, $amountMinusFee);

        try {
            $this->migration()->up();
            $this->fail('The migration should have stopped');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Nothing was changed', $e->getMessage());
        }

        $this->assertEquals([19.99, 0, 19.99], $this->amounts($fine));
        $this->assertEquals([$amount, $fee, $amountMinusFee], $this->amounts($odd));
    }

    /**
     * The largest payment in production is £100, so a second run finds it out of range
     */
    #[Test]
    public function migrating_twice_stops_the_second_time()
    {
        $payment = $this->paymentOf(100, 0, 100);
        $this->migration()->up();

        $this->expectException(RuntimeException::class);

        try {
            $this->migration()->up();
        } finally {
            $this->assertSame([10000, 0, 10000], $this->amounts($payment));
        }
    }

    #[Test]
    public function rolling_back_converts_pence_to_pounds()
    {
        $payment = $this->paymentOf(19.99, 0.35, 19.64);
        $this->migration()->up();

        $this->migration()->down();

        $this->assertEquals([19.99, 0.35, 19.64], $this->amounts($payment));
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_05_100003_store_payment_amounts_in_pence.php');
    }

    /**
     * Inserted directly, as the model would cast the amounts to integers
     */
    private function paymentOf(float $amount, float $fee, float $amountMinusFee): int
    {
        return DB::table('payments')->insertGetId(Payment::factory()->raw([
            'amount' => $amount,
            'fee' => $fee,
            'amount_minus_fee' => $amountMinusFee,
        ]));
    }

    /**
     * @return list<int|float>
     */
    private function amounts(int $paymentId): array
    {
        $payment = DB::table('payments')->where('id', $paymentId)->first(['amount', 'fee', 'amount_minus_fee']);

        return [$payment->amount, $payment->fee, $payment->amount_minus_fee];
    }
}
