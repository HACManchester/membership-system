<?php

namespace Tests\Unit\Support;

use BB\Entities\ProfileData;
use BB\Entities\User;
use BB\Support\MemberAlerts;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberAlertsTest extends TestCase
{
    #[Test]
    public function a_member_missing_everything_gets_every_alert()
    {
        $member = User::factory()->create(['email_verified' => false, 'phone' => '']);

        $this->assertSame(['email-not-verified', 'missing-profile-photo', 'missing-phone'], MemberAlerts::for($member));
    }

    #[Test]
    public function a_pending_photo_counts_as_having_one()
    {
        $member = User::factory()->create(['email_verified' => true, 'phone' => '07700 900123']);
        ProfileData::factory()->create(['user_id' => $member->id, 'new_profile_photo' => true]);

        $this->assertSame([], MemberAlerts::for($member));
    }
}
