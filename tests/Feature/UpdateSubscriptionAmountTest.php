<?php

namespace Tests\Feature;

use BB\Entities\Role;
use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateSubscriptionAmountTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_member_can_update_their_subscription_amount()
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '27',
            ])
            ->assertStatus(302);

        $this->assertEquals(27, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function a_non_numeric_amount_is_rejected()
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => 'twenty',
            ])
            ->assertStatus(302); // Redirect back with flash error

        $this->assertEquals(22, $member->fresh()->monthly_subscription);
    }

    #[Test]
    public function a_member_cannot_go_below_the_minimum_price()
    {
        $member = User::factory()->create(['monthly_subscription' => 22]);

        $this->actingAs($member)
            ->post(route('account.update-sub-payment', $member->id), [
                'monthly_subscription' => '1',
            ])
            ->assertStatus(302);

        $this->assertEquals(22, $member->fresh()->monthly_subscription);
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
            ->assertStatus(302);

        $this->assertEquals(1, $member->fresh()->monthly_subscription);
    }
}
