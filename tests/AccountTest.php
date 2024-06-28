<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

class AccountTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function i_can_view_account_page()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->get('/account/' . $user->id)
            ->seeStatusCode(200)
            ->see($user->name)
            ->see($user->email);
    }

    /** @test */
    public function i_cant_view_an_inactive_member_page()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $user2 = factory('BB\Entities\User')->create(['active' => false]);
        factory('BB\Entities\ProfileData')->create(['user_id' => $user2->id]);

        $this->actingAs($user);

        $this->get('/members/' . $user2->id)
            ->assertRedirectedToRoute('members.index')
            ->assertSessionHas('flash_notification', [
                "message" => "This user's profile is no longer available as they are not an active member.",
                "details" => null,
                "level" => "danger"
            ]);
    }

    /** @test */
    public function i_cant_view_another_account()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $user2 = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user2->id]);

        $this->actingAs($user);

        $this->get('/account/' . $user2->id)
            ->assertResponseStatus(403);
    }

    /** @test */
    public function i_can_see_accounts_on_member_page()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $user2 = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user2->id]);

        $this->actingAs($user);

        $this->get('members')
            ->seeStatusCode(200)
            ->see($user->name)
            ->see($user2->name);
    }

    /** @test */
    public function guests_cant_see_members_list()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $user2 = factory('BB\Entities\User')->create(['profile_private' => true]);
        factory('BB\Entities\ProfileData')->create(['user_id' => $user2->id]);

        $this->get('members')
            ->seeStatusCode(302);
    }

    /** @test */
    public function member_cant_see_private_accounts_on_member_page()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $user2 = factory('BB\Entities\User')->create(['profile_private' => true]);
        factory('BB\Entities\ProfileData')->create(['user_id' => $user2->id]);

        $this->actingAs($user);

        $this->get('members')
            ->seeStatusCode(200)
            ->see($user->name)
            ->see($user2->name, true);
    }

    /** @test */
    public function i_can_edit_my_profile()
    {
        $user = factory('BB\Entities\User')->create();
        factory('BB\Entities\ProfileData')->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->visit('/account/' . $user->id . '/profile/edit')
            ->see('Fill in your profile')
            //->select(['skill1', 'skill2'], 'skills[]')
            ->press('Save')
            ->see($user->display_name)
            ->dontSee('Fill in your profile');

        //$this->seeInDatabase('users', ['email' => $email, 'given_name' => $firstName]);
    }
}
