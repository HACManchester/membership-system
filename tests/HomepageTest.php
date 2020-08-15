<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

Class HomepageTest extends TestCase
{

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