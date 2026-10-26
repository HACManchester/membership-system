<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A member can have no monthly subscription amount, e.g. one who left before
 * setting it. The account page's payment panels rounded it, and round(null)
 * is deprecated since PHP 8.1 and will be an error in PHP 9.
 */
class AccountPageWithoutSubscriptionTest extends TestCase
{
    #[DataProvider('statusesShowingAPaymentPanel')]
    #[Test]
    public function the_payment_panels_render_without_a_subscription_amount(string $status, string $panelText)
    {
        $member = $this->memberWithoutSubscription(['status' => $status, 'active' => false]);

        $this->actingAs($member)
            ->get(route('account.show', $member->id))
            ->assertOk()
            ->assertSee($panelText, false);
    }

    public static function statusesShowingAPaymentPanel(): array
    {
        $notSet = "You haven't set how much you'd like to pay per month yet.";
        $problem = 'There is a problem with your subscription payment';

        return [
            'left (rejoin)' => ['left', $notSet],
            'leaving' => ['leaving', $notSet],
            'payment warning' => ['payment-warning', $problem],
            'suspended' => ['suspended', $problem],
        ];
    }

    #[Test]
    public function an_admin_can_view_a_cash_member_without_a_subscription_amount()
    {
        $admin = factory(User::class)->state('admin')->create();
        $member = $this->memberWithoutSubscription(['status' => 'active', 'active' => true, 'payment_method' => 'cash']);

        $this->actingAs($admin)
            ->get(route('account.show', $member->id))
            ->assertOk()
            ->assertSee('Record A &pound;0 Cash Subscription Payment', false);
    }

    private function memberWithoutSubscription(array $attributes): User
    {
        $member = factory(User::class)->create(['monthly_subscription' => null] + $attributes);
        ProfileData::factory()->create(['user_id' => $member->id]);

        return $member;
    }
}
