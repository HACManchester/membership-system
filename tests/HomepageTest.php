<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

Class HomepageTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function i_can_visit_home_page()
    {
        $this->visit('/')
            ->see('Hackspace Manchester')
            ->see('Become a member')
            ->see('Login')
            ->see('www.hacman.org.uk');
    }


}