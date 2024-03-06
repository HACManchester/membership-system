<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

class NodeTest extends BrowserKitTestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;

    /**
     * @test
     */
    public function boot_endpoint_updates_db()
    {
        $node = factory('BB\Entities\ACSNode')->create();
        //echo $node->id;

        $this->assertNull($node->last_boot);

        //$this->post('/acs/node/boot', ['Accept' => 'application/json', 'ApiKey' => $node->api_key]);
        //$this->seeStatusCode(200);

        //$nodeUpdated = \BB\Entities\ACSNode::where('api_key', $node->api_key)->first();

        //dd($nodeUpdated);
        //echo $nodeUpdated->id;
        //$this->assertNotNull($nodeUpdated->last_boot);
    }
}
