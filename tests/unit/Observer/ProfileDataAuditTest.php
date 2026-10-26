<?php

namespace Tests\Unit\Observer;

use BB\Entities\AuditLog;
use BB\Entities\ProfileData;
use BB\Entities\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * UserAuditObserver watches both User and ProfileData. ProfileData's
 * registration moved to ObserverServiceProvider for Laravel 13 (observe()
 * instantiates the model, which its own boot() can no longer do), and the
 * observer used to file profile changes under the profile row's id instead of
 * the member's.
 */
class ProfileDataAuditTest extends TestCase
{
    #[Test]
    public function changing_an_audited_profile_field_is_recorded_against_the_member()
    {
        // Make sure the profile's id and its member's id differ
        User::factory()->count(2)->create();
        $member = User::factory()->create();
        $profile = ProfileData::factory()->create(['user_id' => $member->id, 'profile_photo_on_wall' => 0]);
        $this->assertNotEquals($member->id, $profile->id);

        $profile->update(['profile_photo_on_wall' => 1]);

        $log = AuditLog::where('description', 'like', '%profile_photo_on_wall [is 1, was 0]%')->first();
        $this->assertNotNull($log, 'the profile change should be audited');
        $this->assertSame($member->id, (int) $log->user_id, 'against the member, not the profile row');
    }

    #[Test]
    public function changing_an_audited_member_field_is_recorded_against_the_member()
    {
        $member = User::factory()->create(['trusted' => 0]);

        $member->update(['trusted' => 1]);

        $log = AuditLog::where('description', 'like', '%trusted [is 1, was 0]%')->first();
        $this->assertNotNull($log);
        $this->assertSame($member->id, (int) $log->user_id);
    }
}
