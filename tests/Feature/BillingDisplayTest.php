<?php

namespace Tests\Feature;

use BB\Entities\ProfileData;
use BB\Entities\Role;
use BB\Entities\SubscriptionCharge;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * How a member's subscription, payment method, balance and subscription
 * charges are written out on the account and finance pages.
 */
class BillingDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_account_page_summarises_a_direct_debit_subscription()
    {
        $member = $this->member(['payment_method' => 'gocardless', 'monthly_subscription' => 1500, 'payment_day' => 5]);

        $this->actingAs($member)->get(route('account.show', $member->id))
            ->assertOk()
            ->assertSee("💳 Subscription: £15.00/mo by Direct Debit on the 5th\n", false);
    }

    #[Test]
    public function the_account_page_leaves_out_the_payment_day_when_there_is_none()
    {
        $member = $this->member(['payment_method' => 'standing-order', 'monthly_subscription' => 2040, 'payment_day' => 0]);

        $this->actingAs($member)->get(route('account.show', $member->id))
            ->assertOk()
            ->assertSee("💳 Subscription: £20.40/mo by Standing Order\n", false);
    }

    #[Test]
    public function the_account_page_has_no_subscription_summary_while_setting_up()
    {
        $member = $this->member(['status' => 'setting-up', 'payment_method' => 'gocardless', 'monthly_subscription' => 1500, 'payment_day' => 5]);

        $this->actingAs($member)->get(route('account.show', $member->id))
            ->assertOk()
            ->assertSee("💳 Subscription: \n", false);
    }

    #[Test]
    public function the_account_page_lists_the_member_subscription_charges()
    {
        $member = $this->member(['payment_method' => 'gocardless', 'monthly_subscription' => 1500]);
        $this->charge($member, ['status' => 'paid', 'charge_date' => '2024-03-01', 'payment_date' => '2024-03-04', 'amount' => 1500]);
        $this->charge($member, ['status' => 'pending', 'charge_date' => '2024-04-01', 'amount' => 1500]);
        $this->charge($member, ['status' => 'processing', 'charge_date' => '2024-05-01', 'amount' => 1500]);

        $this->actingAs($member)->get(route('account.show', $member->id))
            ->assertOk()
            ->assertSee(<<<'HTML'
                        <tr class="success">
                            <td>Mar 1, 2024</td>
                            <td>£15.00</td>
                            <td>Paid</td>
                            <td>Mar 4, 2024</td>
                        </tr>
                HTML, false)
            ->assertSee(<<<'HTML'
                        <tr class="">
                            <td>Apr 1, 2024</td>
                            <td></td>
                            <td>Pending</td>
                            <td></td>
                        </tr>
                HTML, false)
            ->assertSee(<<<'HTML'
                        <tr class="info">
                            <td>May 1, 2024</td>
                            <td>£15.00</td>
                            <td>Processing</td>
                            <td></td>
                        </tr>
                HTML, false);
    }

    #[Test]
    public function the_member_list_shows_each_member_payment_method_and_expiry()
    {
        $admin = User::factory()->admin()->create();
        ProfileData::factory()->create(['user_id' => $admin->id]);
        $directDebit = $this->member(['payment_method' => 'gocardless-variable', 'subscription_expires' => '2025-03-05']);
        $none = $this->member(['payment_method' => '', 'subscription_expires' => null]);
        $cash = $this->member(['payment_method' => 'cash']);

        $response = $this->actingAs($admin)->get(route('account.index'))->assertOk();

        $response->assertSeeInOrder([$directDebit->email, 'Direct Debit<br />', 'Expires: Mar 5, 2025</span>'], false);
        $response->assertSeeInOrder([$none->email, '-<br />', 'Expires: -</span>'], false);
        $response->assertSeeInOrder([$cash->email, 'cash<br />'], false);
    }

    #[Test]
    public function the_balances_page_shows_each_member_balance_in_pounds()
    {
        $inCredit = $this->member(['cash_balance' => 1200]);
        $inDebt = $this->member(['cash_balance' => -350]);

        $this->actingAs($this->financeMember())->get(route('payments.balances'))
            ->assertOk()
            ->assertSeeInOrder([e($inCredit->name), '<td>£12.00</td>', e($inDebt->name), '<td>£-3.50</td>'], false);
    }

    #[Test]
    public function the_subscription_charges_page_describes_each_charge()
    {
        $member = $this->member(['payment_method' => 'standing-order']);
        $this->charge($member, ['status' => 'paid', 'charge_date' => '2024-03-01', 'payment_date' => '2024-03-04', 'amount' => 1500]);

        $this->actingAs($this->financeMember())->get(route('payments.sub-charges'))
            ->assertOk()
            ->assertSeeInOrder([
                '<td>Mar 1, 2024</td>',
                e($member->name),
                '<td>Mar 4, 2024</td>',
                '<td>Paid</td>',
                '<td>£15.00</td>',
                'Standing Order',
            ], false);
    }

    #[Test]
    public function the_account_edit_form_is_filled_with_the_member_phone_number()
    {
        $member = $this->member(['phone' => '07700 900123']);

        $this->actingAs($member)->get(route('account.edit', $member->id))
            ->assertOk()
            ->assertSee('value="07700 900123"', false);
    }

    private function member(array $attributes = []): User
    {
        $member = User::factory()->create($attributes);
        ProfileData::factory()->create(['user_id' => $member->id]);

        return $member;
    }

    private function financeMember(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('finance'));

        return $user;
    }

    private function charge(User $member, array $attributes): SubscriptionCharge
    {
        return SubscriptionCharge::factory()->create(['user_id' => $member->id] + $attributes);
    }
}
