<?php

namespace Tests\Feature;

use BB\Entities\Address;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use Tests\TestCase;

/**
 * Starting a Direct Debit sends the member to GoCardless's hosted page with
 * their details prefilled. Members without an address on file — some left
 * members, who need this to rejoin, and some who never finished signing up —
 * used to get a 500 here, because the address was read unconditionally.
 */
class DirectDebitSetupTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $goCardless;

    /** @var array|null the params sent to GoCardless's redirect flow */
    private $sentParams;

    protected function setUp(): void
    {
        parent::setUp();

        $this->goCardless = $this->createMock(GoCardlessHelper::class);
        $this->goCardless->method('newPreAuthUrl')->willReturnCallback(function ($user, $params) {
            $this->sentParams = $params;

            return 'https://pay-sandbox.gocardless.com/flow/RE123';
        });
        $this->app->instance(GoCardlessHelper::class, $this->goCardless);
    }

    /** @test */
    public function a_member_with_an_address_has_it_prefilled()
    {
        $member = $this->rejoiningMember();
        $this->giveAddress($member);

        $this->actingAs($member)
            ->get(route('account.subscription.create', $member->id))
            ->assertRedirect('https://pay-sandbox.gocardless.com/flow/RE123');

        $this->assertSame([
            'given_name' => 'Ada',
            'family_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'address_line1' => '1 Test Street',
            'address_line2' => 'Ancoats',
            'city' => 'Manchester',
            'postal_code' => 'M4 7HR',
            'country_code' => 'GB',
        ], $this->sentParams['prefilled_customer']);
        $this->assertSame('user-token-' . $member->id, $this->sentParams['session_token']);
        $this->assertStringStartsWith('https://', $this->sentParams['success_redirect_url']);
    }

    /** @test */
    public function a_member_without_an_address_can_still_start_a_direct_debit()
    {
        $member = $this->rejoiningMember();

        $this->actingAs($member)
            ->get(route('account.subscription.create', $member->id))
            ->assertRedirect('https://pay-sandbox.gocardless.com/flow/RE123');

        // They fill their address in on GoCardless's page instead
        $this->assertSame([
            'given_name' => 'Ada',
            'family_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'country_code' => 'GB',
        ], $this->sentParams['prefilled_customer']);
    }

    /**
     * migrateDD() cancels the legacy subscription before redirecting, so a
     * crash here used to leave the member with no Direct Debit at all.
     *
     * @test
     */
    public function a_legacy_member_without_an_address_can_migrate_to_variable_billing()
    {
        $member = $this->rejoiningMember();
        $member->forceFill([
            'status' => 'active',
            'active' => true,
            'payment_method' => 'gocardless',
            'subscription_id' => 'SB_LEGACY_1',
        ])->save();

        $this->goCardless->method('cancelSubscription')->willReturn((object) ['status' => 'cancelled']);

        $this->actingAs($member)
            ->post(route('account.payment.gocardless-migrate'))
            ->assertRedirect('https://pay-sandbox.gocardless.com/flow/RE123');

        $this->assertArrayNotHasKey('address_line1', $this->sentParams['prefilled_customer']);
    }

    private function rejoiningMember()
    {
        return factory(User::class)->create([
            'given_name' => 'Ada',
            'family_name' => 'Lovelace',
            'email' => 'ada@example.test',
            'status' => 'left',
            'active' => false,
            'monthly_subscription' => 22,
        ]);
    }

    private function giveAddress(User $member)
    {
        $address = new Address();
        $address->forceFill([
            'user_id' => $member->id,
            'line_1' => '1 Test Street',
            'line_2' => 'Ancoats',
            'line_3' => 'Manchester',
            'line_4' => '',
            'postcode' => 'M4 7HR',
            'approved' => true,
            'hash' => md5('address'),
        ])->save();
    }
}
