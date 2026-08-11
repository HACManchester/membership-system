<?php

use BB\Entities\Payment;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use Carbon\Carbon;
use Tests\TestCase;

class AuditSubscriptionChargesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $telegramHelper = $this->createMock(\BB\Helpers\TelegramHelper::class);
        $this->app->instance(\BB\Helpers\TelegramHelper::class, $telegramHelper);
    }

    public function testReportsUncollectableChargesWithoutChangingThem()
    {
        $charge = $this->orphanedCharge();

        $this->artisan('bb:audit-sub-charges')
            ->expectsOutput('Uncollectable outstanding charges: 1')
            ->assertExitCode(0);

        $this->assertEquals('due', $charge->fresh()->status);
    }

    public function testFixCancelsUncollectableCharges()
    {
        $charge = $this->orphanedCharge();

        $this->artisan('bb:audit-sub-charges', ['--fix' => true])->assertExitCode(0);

        $this->assertEquals('cancelled', $charge->fresh()->status);
    }

    public function testLeavesChargesForBillableMembersAlone()
    {
        $user = factory(User::class)->create([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless-variable',
            'mandate_id' => 'MD123456789',
        ]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => Carbon::now(),
            'status' => 'due',
        ]);

        $this->artisan('bb:audit-sub-charges', ['--fix' => true])
            ->expectsOutput('Uncollectable outstanding charges: 0')
            ->assertExitCode(0);

        $this->assertEquals('due', $charge->fresh()->status);
    }

    public function testReportsChargesCollectedLongAfterTheirChargeDate()
    {
        $this->backdatedCollection(Carbon::now()->subMonths(6), Carbon::now());

        $this->artisan('bb:audit-sub-charges')
            ->expectsOutput('Charges collected more than 35 days after their charge date, since 2026-01-01: 1')
            ->assertExitCode(0);
    }

    public function testDoesNotReportAChargeCollectedOnTime()
    {
        $this->backdatedCollection(Carbon::now()->subDays(3), Carbon::now());

        $this->artisan('bb:audit-sub-charges')
            ->expectsOutput('Charges collected more than 35 days after their charge date, since 2026-01-01: 0')
            ->assertExitCode(0);
    }

    /**
     * A charge whose money moved well after the date it was raised for
     *
     * @param Carbon $chargeDate
     * @param Carbon $collectedAt
     */
    private function backdatedCollection(Carbon $chargeDate, Carbon $collectedAt)
    {
        $user = factory(User::class)->create(['status' => 'active', 'active' => true]);

        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => $chargeDate,
            'status' => 'paid',
        ]);

        factory(Payment::class)->create([
            'user_id' => $user->id,
            'reason' => 'subscription',
            'source' => 'gocardless-variable',
            'source_id' => 'PM_' . $charge->id,
            'reference' => $charge->id,
            'status' => 'paid',
            'paid_at' => $collectedAt,
        ]);
    }

    public function testFixCancelsUncollectableChargesHoweverOldTheyAre()
    {
        // The cutoff is about who is owed a refund - an ancient charge is still waiting
        // to be billed the moment the member sets up a mandate, so it still wants clearing
        $charge = $this->orphanedCharge(Carbon::parse('2021-06-01'));

        $this->artisan('bb:audit-sub-charges', ['--fix' => true])
            ->expectsOutput('Uncollectable outstanding charges: 1')
            ->assertExitCode(0);

        $this->assertEquals('cancelled', $charge->fresh()->status);
    }

    public function testIgnoresCollectionsFromBeforeTheCutoff()
    {
        $this->backdatedCollection(Carbon::parse('2021-01-01'), Carbon::parse('2021-06-01'));

        $this->artisan('bb:audit-sub-charges')
            ->expectsOutput('Charges collected more than 35 days after their charge date, since 2026-01-01: 0')
            ->expectsOutput('  (1 older than 2026-01-01 not shown)')
            ->assertExitCode(0);
    }

    public function testTheCollectionCutoffCanBeWidened()
    {
        $this->backdatedCollection(Carbon::parse('2021-01-01'), Carbon::parse('2021-06-01'));

        $this->artisan('bb:audit-sub-charges', ['--since' => '2020-01-01'])
            ->expectsOutput('Charges collected more than 35 days after their charge date, since 2020-01-01: 1')
            ->assertExitCode(0);
    }

    /**
     * A charge left behind by a cancelled mandate - the member has no way to pay it
     *
     * @param Carbon|null $chargeDate
     * @return SubscriptionCharge
     */
    private function orphanedCharge(Carbon $chargeDate = null)
    {
        $user = factory(User::class)->create([
            'status' => 'leaving',
            'active' => true,
            'payment_method' => '',
            'mandate_id' => '',
        ]);

        return factory(SubscriptionCharge::class)->create([
            'user_id' => $user->id,
            'charge_date' => $chargeDate ?: Carbon::now()->subMonths(2),
            'status' => 'due',
        ]);
    }
}
