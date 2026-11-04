<?php

namespace Tests\Unit\Repo;

use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Repo\SubscriptionChargeRepository;
use BB\Support\SubscriptionChargeStatus;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionChargeRepositoryTest extends TestCase
{
    #[Test]
    public function a_charge_billed_immediately_is_billed_with_an_idempotency_key_identifying_it()
    {
        $user = User::factory()->create(['mandate_id' => 'MD123456789']);

        $sentKey = null;
        $goCardless = $this->createMock(GoCardlessHelper::class);
        $goCardless->method('getNameFromReason')->willReturn('Monthly subscription');
        $goCardless->expects($this->once())
            ->method('newBill')
            ->with(
                'MD123456789',
                2200,
                'Monthly subscription',
                null,
                $this->callback(function (string $key) use (&$sentKey) {
                    $sentKey = $key;

                    return true;
                })
            )
            ->willReturn((object)['id' => 'PM123456789', 'status' => 'pending_submission']);
        $this->app->instance(GoCardlessHelper::class, $goCardless);

        $charge = app(SubscriptionChargeRepository::class)
            ->createChargeAndBillDD($user->id, Carbon::parse('2026-10-01'), 22, 'due', 'MD123456789');

        $this->assertSame("subscription-charge-{$charge->id}-user-{$user->id}-2026-10-01-MD123456789", $sentKey);
    }

    #[Test]
    public function a_new_charge_has_no_payment_date()
    {
        $user = User::factory()->create();

        $charge = app(SubscriptionChargeRepository::class)->createCharge($user->id, Carbon::parse('2026-10-01'), 22);

        $this->assertNull($charge->refresh()->payment_date);
    }

    #[Test]
    public function cancelling_outstanding_charges_leaves_charges_already_being_collected()
    {
        $user = User::factory()->create();
        $charges = SubscriptionCharge::factory()->for($user)->count(4)
            ->sequence(['status' => 'pending'], ['status' => 'due'], ['status' => 'processing'], ['status' => 'paid'])
            ->create();

        app(SubscriptionChargeRepository::class)->cancelOutstandingCharges($user->id);

        $this->assertSame(
            [SubscriptionChargeStatus::Cancelled, SubscriptionChargeStatus::Cancelled, SubscriptionChargeStatus::Processing, SubscriptionChargeStatus::Paid],
            $charges->map(fn (SubscriptionCharge $charge) => $charge->fresh()->status)->all()
        );
    }

    #[Test]
    public function a_charge_being_collected_counts_as_outstanding()
    {
        $user = User::factory()->create();
        SubscriptionCharge::factory()->for($user)->create(['status' => 'processing']);

        $this->assertTrue(app(SubscriptionChargeRepository::class)->hasOutstandingCharges($user->id));
    }

    #[Test]
    public function paid_and_cancelled_charges_are_not_outstanding()
    {
        $user = User::factory()->create();
        SubscriptionCharge::factory()->for($user)->count(2)
            ->sequence(['status' => 'paid'], ['status' => 'cancelled'])
            ->create();

        $this->assertFalse(app(SubscriptionChargeRepository::class)->hasOutstandingCharges($user->id));
    }
}
