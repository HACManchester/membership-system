<?php

    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

    class GroupTest extends BrowserKitTestCase
    {
        use DatabaseMigrations;

        /** @test */
        public function i_can_view_the_groups_nav_link()
        {
            $user = factory('BB\Entities\User')->create();
            $this->actingAs($user);

            $this->withoutMiddleware()
                ->visit('/')
                ->see('Teams')
                ->click('Teams')
                ->seePageIs('groups');
        }

        /** @test */
        public function i_can_view_the_groups()
        {
            $user = factory('BB\Entities\User')->create();
            $this->actingAs($user);
            $role = factory('BB\Entities\Role')->create();

            $this->withoutMiddleware()
                ->visit('/groups')
                ->see($role->title)
                ->see($role->description);
        }

        /** @test */
        public function i_can_view_a_single_group()
        {
            $user = factory('BB\Entities\User')->create();
            $this->actingAs($user);
            $role = factory('BB\Entities\Role')->create();

            $this->withoutMiddleware()
                ->visit('/groups')
                ->see($role->title)
                ->see($role->description)
                ->click($role->title)
                ->seePageIs('/groups/' . $role->name);
        }
    }
