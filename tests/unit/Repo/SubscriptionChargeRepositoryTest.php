<?php

use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Repo\SubscriptionChargeRepository;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionChargeRepositoryTest extends TestCase
{
    #[Test]
    public function a_charge_billed_immediately_is_billed_with_an_idempotency_key_identifying_it()
    {
        $user = factory(User::class)->create(['mandate_id' => 'MD123456789']);

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
}
