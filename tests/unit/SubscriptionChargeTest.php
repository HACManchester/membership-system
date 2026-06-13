<?php

use BB\Events\SubscriptionChargePaid;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubscriptionChargeTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function marking_a_charge_as_paid_updates_it_and_fires_an_event()
    {
        /** @var \BB\Repo\SubscriptionChargeRepository $repo */
        $repo = $this->app->make(\BB\Repo\SubscriptionChargeRepository::class);

        $user = factory('BB\Entities\User')->create();
        $paymentDate = Carbon::now();
        $charge = $repo->createCharge($user->id, Carbon::now(), 1700);

        Event::fake([SubscriptionChargePaid::class]);

        $repo->markChargeAsPaid($charge->id, $paymentDate);

        $charge->refresh();
        $this->assertEquals('paid', $charge->status);
        $this->assertTrue($charge->payment_date->isSameDay($paymentDate));

        Event::assertDispatched(SubscriptionChargePaid::class, function ($event) use ($charge) {
            return $event->subscriptionCharge->id === $charge->id;
        });
    }
}
