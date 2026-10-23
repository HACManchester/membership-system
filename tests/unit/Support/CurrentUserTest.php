<?php

namespace Tests\Unit\Support;

use BB\Entities\ApiKey;
use BB\Entities\User;
use BB\Support\CurrentUser;
use Illuminate\Auth\AuthenticationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrentUserTest extends TestCase
{
    #[Test]
    public function it_returns_the_signed_in_member()
    {
        $member = factory(User::class)->create();
        $this->actingAs($member);

        $this->assertTrue($member->is(CurrentUser::get()));
        $this->assertTrue($member->is(CurrentUser::find()));
    }

    #[Test]
    public function a_guest_has_no_current_user()
    {
        $this->assertNull(CurrentUser::find());

        $this->expectException(AuthenticationException::class);
        CurrentUser::get();
    }

    #[Test]
    public function an_api_key_is_not_a_member()
    {
        $this->actingAs(ApiKey::create(['api_token' => 'door-system', 'description' => 'Door system']));

        $this->assertNull(CurrentUser::find());

        $this->expectException(AuthenticationException::class);
        CurrentUser::get();
    }
}
