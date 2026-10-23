<?php

namespace Tests\Unit\Observer;

use BB\Entities\AuditLog;
use BB\Entities\ProfileData;
use BB\Entities\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ProfileData's audit observer used to be registered in its own boot(), which
 * Laravel 13 forbids (observe() instantiates the model mid-boot). It's now
 * registered by ObserverServiceProvider.
 */
class ProfileDataAuditTest extends TestCase
{
    #[Test]
    public function changing_an_audited_profile_field_is_recorded()
    {
        $member = factory(User::class)->create();
        $profile = factory(ProfileData::class)->create(['user_id' => $member->id, 'profile_photo_on_wall' => 0]);

        $profile->update(['profile_photo_on_wall' => 1]);

        $this->assertTrue(
            AuditLog::where('description', 'like', '%profile_photo_on_wall [is 1, was 0]%')->exists(),
            'the profile change should be audited'
        );
    }
}
