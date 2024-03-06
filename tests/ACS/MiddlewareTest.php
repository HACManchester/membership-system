<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;

class MiddlewareTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function test_endpoint_returns_200()
    {
        $this->withoutMiddleware();

        $this->get('/acs/test', ['Accept' => 'application/json']);

        $this->seeStatusCode(200);
    }

    /**
     * @test
     */
    public function invalid_api_key_returns_403()
    {
        $this->get('/acs/test', ['Accept' => 'application/json', 'ApiKey' => 'invalidkey']);

        $this->seeStatusCode(403);
    }

    /**
     * @test
     */
    public function valid_api_key_returns_200()
    {
        $node = factory('BB\Entities\ACSNode')->create();
        $this->get('/acs/test', ['Accept' => 'application/json', 'ApiKey' => $node->api_key]);

        $this->seeStatusCode(200);
    }
}
