<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountUpdateTest extends TestCase
{
    /** Fields only admin or system code may set, with values a member might try to give themselves */
    private const ADMIN_MANAGED_FIELDS = [
        'status' => 'active',
        'active' => 1,
        'banned' => 1,
        'banned_reason' => 'self-inflicted',
        'banned_date' => '2026-01-01 00:00:00',
        'key_holder' => 1,
        'trusted' => 1,
        'induction_completed' => 1,
        'key_deposit_payment_id' => 1,
        'payment_method' => 'cash',
        'gift' => 'FREEBIE',
        'seen_at' => '2026-01-01 00:00:00',
        'hash' => 'chosen-hash',
    ];

    #[Test]
    public function a_member_can_update_their_own_details()
    {
        $member = User::factory()->create(['given_name' => 'Ada']);

        $this->actingAs($member)
            ->put(route('account.update', $member->id), $this->detailsFor($member, ['given_name' => 'Augusta']))
            ->assertRedirect(route('account.show', $member->id));

        $this->assertSame('Augusta', $member->fresh()->given_name);
    }

    /**
     * Guards the controller's Request::only() list. UserMassAssignmentTest covers User::$fillable itself.
     */
    #[Test]
    public function a_member_can_submit_a_new_address_for_approval()
    {
        $member = User::factory()->create();
        $address = ['line_1' => '1 Example Street', 'line_2' => '', 'line_3' => 'Manchester', 'line_4' => '', 'postcode' => 'm1 1aa'];

        $this->actingAs($member)
            ->put(route('account.update', $member->id), $this->detailsFor($member, ['online_only' => '0', 'phone' => '07700900123', 'emergency_contact' => 'Ada, 07700900456', 'address' => $address]))
            ->assertRedirect(route('account.show', $member->id));

        $this->assertDatabaseHas('user_address', [
            'user_id' => $member->id,
            'line_1' => '1 Example Street',
            'postcode' => 'M1 1AA',
            'approved' => false,
        ]);
    }

    #[Test]
    public function a_member_cannot_set_admin_managed_fields_on_their_own_account()
    {
        $member = User::factory()->create([
            'status' => 'setting-up',
            'active' => false,
            'key_holder' => false,
            'trusted' => false,
            'induction_completed' => false,
            'payment_method' => null,
            'hash' => 'original-hash',
        ]);
        // seen_at is left out: every signed-in request moves it on to now
        $fields = array_keys(Arr::except(self::ADMIN_MANAGED_FIELDS, 'seen_at'));
        $before = $member->fresh()->only($fields);

        $this->actingAs($member)
            ->put(route('account.update', $member->id), $this->detailsFor($member, self::ADMIN_MANAGED_FIELDS))
            ->assertRedirect(route('account.show', $member->id));

        $after = $member->fresh();
        $this->assertSame($before, $after->only($fields));
        $this->assertTrue($after->seen_at->isToday());
    }

    /**
     * A valid account form submission for the member, plus any extra fields.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function detailsFor(User $member, array $extra = []): array
    {
        return array_merge([
            'given_name' => $member->given_name,
            'family_name' => $member->family_name,
            'email' => $member->email,
            'display_name' => $member->display_name,
            'online_only' => '1',
            'suppress_real_name' => '0',
        ], $extra);
    }
}
