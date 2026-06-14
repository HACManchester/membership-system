<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    /** @test */
    public function the_homepage_loads()
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Hackspace Manchester')
            ->assertSee('Become a member');
    }
}
