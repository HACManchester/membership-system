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

        // The minimum is configured in pence; amounts are posted in pounds
        config(['membership.prices.minimum' => 1700]);
    }

    #[Test]
    #[TestWith(['27'])]
    #[TestWith(['17'])] // exactly the minimum
    public function a_member_can_update_their_subscription_amount(string $amount)
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => $amount,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('account.show', $member->id));

        $this->assertSame((int) $amount, $member->fresh()->monthly_subscription);
    }

    #[Test]
    #[TestWith(['22.50', 'Please enter a whole number of pounds.'])]
    #[TestWith(['twenty', 'Please enter a whole number of pounds.'])]
    #[TestWith(['', 'Please enter a valid amount in pounds.'])]
    public function an_amount_that_is_not_whole_pounds_is_rejected(string $amount, string $message)
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => $amount,
            ])
            ->assertSessionHasErrors(['monthly_subscription' => $message]);

        $this->assertSame(22, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function a_member_cannot_go_below_the_minimum_price()
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '16',
            ])
            ->assertSessionHasErrors([
                'monthly_subscription' => 'The minimum subscription is £17.00, please contact the board for a lower amount. board@hacman.org.uk',
            ]);

        $this->assertSame(22, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function an_admin_can_set_an_amount_below_the_minimum()
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::findByName('admin'));

        $this->actingAs($admin)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('account.show', $member->id));

        $this->assertSame(1, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function a_member_cannot_change_another_members_amount()
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);
        $otherMember = User::factory()->create();

        $this->actingAs($otherMember)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '27',
            ])
            ->assertForbidden();

        $this->assertSame(22, $member->fresh()->monthly_subscription);
    }
}
