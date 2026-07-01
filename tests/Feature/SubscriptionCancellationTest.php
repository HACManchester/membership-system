<?php

namespace Tests\Feature;

use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use Carbon\Carbon;
use GoCardlessPro\Core\Exception\InvalidStateException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionCancellationTest extends TestCase
{
    use RefreshDatabase;

    private $goCardless;

    public function setUp(): void
    {
        parent::setUp();

        $this->goCardless = $this->createMock(GoCardlessHelper::class);
        $this->app->instance(GoCardlessHelper::class, $this->goCardless);
    }

    /** @test */
    public function a_variable_dd_member_can_cancel_and_their_charges_are_cancelled()
    {
        $member = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless-variable',
            'mandate_id' => 'MD123',
        ]);
        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $member->id,
            'charge_date' => Carbon::now()->addDays(5),
            'status' => 'pending',
        ]);

        $this->goCardless->method('cancelPreAuth')->with('MD123')->willReturn(true);

        $this->actingAs($member)
            ->delete(route('account.subscription.destroy', [$member->id, 0]))
            ->assertStatus(302);

        $member->refresh();
        $this->assertEquals('leaving', $member->status);
        $this->assertEmpty($member->mandate_id);
        $this->assertEmpty($member->payment_method);

        $charge->refresh();
        $this->assertEquals('cancelled', $charge->status);
    }

    /** @test */
    public function a_legacy_dd_cancellation_failure_does_not_cancel_locally()
    {
        $member = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless',
            'subscription_id' => 'SB123',
        ]);

        $this->goCardless->method('cancelSubscription')
            ->willThrowException(new \Exception('GoCardless is unavailable'));

        $this->actingAs($member)
            ->delete(route('account.subscription.destroy', [$member->id, 0]))
            ->assertStatus(302);

        // GoCardless would keep collecting, so nothing may change locally
        $member->refresh();
        $this->assertEquals('active', $member->status);
        $this->assertEquals('gocardless', $member->payment_method);
        $this->assertEquals('SB123', $member->subscription_id);
    }

    /** @test */
    public function a_legacy_dd_already_cancelled_at_gocardless_cancels_locally()
    {
        $member = factory(User::class)->create([
            'status' => 'active',
            'payment_method' => 'gocardless',
            'subscription_id' => 'SB123',
        ]);
        $charge = factory(SubscriptionCharge::class)->create([
            'user_id' => $member->id,
            'charge_date' => Carbon::now()->addDays(5),
            'status' => 'due',
        ]);

        $this->goCardless->method('cancelSubscription')
            ->willThrowException(new InvalidStateException('Subscription already cancelled'));

        $this->actingAs($member)
            ->delete(route('account.subscription.destroy', [$member->id, 0]))
            ->assertStatus(302);

        $member->refresh();
        $this->assertEquals('leaving', $member->status);
        $this->assertEmpty($member->subscription_id);

        $charge->refresh();
        $this->assertEquals('cancelled', $charge->status);
    }
}
