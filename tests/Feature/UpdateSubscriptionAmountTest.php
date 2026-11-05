<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UpdateSubscriptionAmountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The minimum is configured in pence; amounts are posted in pounds and stored in pence
        config(['membership.prices.minimum' => 1700]);
    }

    #[Test]
    #[TestWith(['22.50', 2250])]
    #[TestWith(['27', 2700])]
    #[TestWith(['17', 1700])] // exactly the minimum
    #[TestWith(['17.00', 1700])]
    #[TestWith(['19.99', 1999])] // 19.99 * 100 is 1998.9999999999998
    public function a_member_can_update_their_subscription_amount(string $amount, int $stored)
    {
        $member = User::factory()->create(['monthly_subscription' => 2200]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => $amount,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('account.show', $member->id));

        $this->assertSame($stored, $member->fresh()->monthly_subscription);
    }

    #[Test]
    #[TestWith(['22.505'])]
    #[TestWith(['16.991'])] // also under the minimum, which isn't reported as well
    #[TestWith(['twenty'])]
    #[TestWith([''])]
    public function an_amount_that_is_not_pounds_and_pence_is_rejected_with_one_error(string $amount)
    {
        $member = User::factory()->create(['monthly_subscription' => 2200]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => $amount,
            ])
            ->assertSessionHasErrors('monthly_subscription');

        $this->assertSame(
            ['Please enter an amount in pounds, e.g. 22.50.'],
            session('errors')->get('monthly_subscription'),
        );

        $this->assertSame(2200, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function a_member_cannot_go_below_the_minimum_price()
    {
        $member = User::factory()->create(['monthly_subscription' => 2200]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '16.99',
            ])
            ->assertSessionHasErrors([
                'monthly_subscription' => 'The minimum subscription is £17.00, please contact the board for a lower amount. board@hacman.org.uk',
            ]);

        $this->assertSame(2200, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function an_admin_can_set_an_amount_below_the_minimum()
    {
        $member = User::factory()->create(['monthly_subscription' => 2200]);

        $this->actingAs($this->admin())
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '1.50',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('account.show', $member->id));

        $this->assertSame(150, $member->fresh()->monthly_subscription);
    }

    #[Test]
    #[TestWith(['5000.01'])]
    #[TestWith(['30000000'])] // more pence than the column holds
    public function no_one_can_set_an_amount_over_five_thousand_pounds(string $amount)
    {
        $member = User::factory()->create(['monthly_subscription' => 2200]);

        $this->actingAs($this->admin())
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => $amount,
            ])
            ->assertSessionHasErrors([
                'monthly_subscription' => 'The most we can take each month is £5,000.',
            ]);

        $this->assertSame(2200, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function a_member_cannot_change_another_members_amount()
    {
        $member = User::factory()->create(['monthly_subscription' => 2200]);
        $otherMember = User::factory()->create();

        $this->actingAs($otherMember)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '27',
            ])
            ->assertForbidden();

        $this->assertSame(2200, $member->fresh()->monthly_subscription);
    }

    /**
     * The browser enforces the amount inputs' min attribute, so an admin, who
     * may go below the minimum, mustn't get one.
     */
    #[Test]
    #[TestWith([['status' => 'leaving', 'payment_method' => null]])] // the set-up-payment form and the change modal
    #[TestWith([['status' => 'payment-warning', 'payment_method' => null]])] // the payment problem panel and the change modal
    public function only_members_get_the_minimum_on_the_amount_inputs(array $attributes)
    {
        $member = User::factory()->create(['monthly_subscription' => 2250, ...$attributes]);

        $memberInputs = $this->amountInputs($this->actingAs($member)->get(route('account.show', $member->id))->assertOk()->getContent());
        $adminInputs = $this->amountInputs($this->actingAs($this->admin())->get(route('account.show', $member->id))->assertOk()->getContent());

        $this->assertCount(2, $memberInputs);
        $this->assertCount(2, $adminInputs);
        foreach ($memberInputs as $input) {
            $this->assertStringContainsString('min="17.00"', $input);
            $this->assertStringContainsString('step="0.01"', $input);
            $this->assertStringContainsString('value="22.50"', $input);
        }
        foreach ($adminInputs as $input) {
            $this->assertStringNotContainsString('min=', $input);
            $this->assertStringContainsString('step="0.01"', $input);
        }
    }

    /**
     * @return list<string>
     */
    private function amountInputs(string $html): array
    {
        preg_match_all('/<input[^>]*name="monthly_subscription"[^>]*>/', $html, $matches);

        return $matches[0];
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findByName('admin'));

        return $admin;
    }
}
