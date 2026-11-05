<?php

namespace Tests\Feature\Migrations;

use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class StoreSubscriptionAmountsInPenceTest extends TestCase
{
    #[Test]
    public function migrating_converts_subscription_amounts_from_pounds_to_pence()
    {
        $member = $this->memberPaying(22);
        $unset = $this->memberPaying(null);
        $charge = $this->chargeOf(27);
        $cancelled = $this->chargeOf(0);

        $this->migration()->up();

        $this->assertSame(2200, $this->subscription($member));
        $this->assertNull($this->subscription($unset));
        $this->assertSame(2700, $this->chargeAmount($charge));
        $this->assertSame(0, $this->chargeAmount($cancelled));
    }

    #[Test]
    #[TestWith([5001, 22])] // a member's amount out of range, e.g. already in pence
    #[TestWith([22, 5001])] // a charge's
    #[TestWith([-1, 22])]
    public function migrating_changes_nothing_when_an_amount_is_out_of_range(int $subscription, int $chargeAmount)
    {
        $member = $this->memberPaying(22);
        $odd = $this->memberPaying($subscription);
        $charge = $this->chargeOf($chargeAmount);

        try {
            $this->migration()->up();
            $this->fail('The migration should have stopped');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Nothing was changed', $e->getMessage());
        }

        $this->assertSame(22, $this->subscription($member));
        $this->assertSame($subscription, $this->subscription($odd));
        $this->assertSame($chargeAmount, $this->chargeAmount($charge));
    }

    /**
     * The largest subscription in production is £1000, so a second run finds it out of range
     */
    #[Test]
    public function migrating_twice_stops_the_second_time()
    {
        // As left by the first run
        $member = $this->memberPaying(2200);
        $this->memberPaying(100000);

        $this->expectException(RuntimeException::class);

        try {
            $this->migration()->up();
        } finally {
            $this->assertSame(2200, $this->subscription($member));
        }
    }

    #[Test]
    public function rolling_back_converts_pence_to_whole_pounds()
    {
        $member = $this->memberPaying(2200);
        $withPence = $this->memberPaying(2250);
        $charge = $this->chargeOf(2700);

        $this->migration()->down();

        $this->assertSame(22, $this->subscription($member));
        $this->assertSame(23, $this->subscription($withPence), 'pence are rounded away');
        $this->assertSame(27, $this->chargeAmount($charge));
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_05_100002_store_subscription_amounts_in_pence.php');
    }

    /**
     * Inserted directly, as the model would cast the amount to an integer
     */
    private function memberPaying(?int $amount): int
    {
        $id = User::factory()->create()->id;
        DB::table('users')->where('id', $id)->update(['monthly_subscription' => $amount]);

        return $id;
    }

    private function chargeOf(int $amount): int
    {
        return DB::table('subscription_charge')->insertGetId(SubscriptionCharge::factory()->raw(['amount' => $amount]));
    }

    private function subscription(int $userId): ?int
    {
        return DB::table('users')->where('id', $userId)->value('monthly_subscription');
    }

    private function chargeAmount(int $chargeId): int
    {
        return DB::table('subscription_charge')->where('id', $chargeId)->value('amount');
    }
}
