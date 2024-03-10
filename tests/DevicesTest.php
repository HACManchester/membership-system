<?php

use BB\Entities\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

Class DevicesTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function i_can_visit_the_devices_page()
    {
        $device = factory('BB\Entities\ACSNode')->create();
        $this->withoutMiddleware()
            ->visit('/devices')
            ->see('Devices')
            ->see($device->name);
    }


}