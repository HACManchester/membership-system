<?php

namespace Tests\Unit\Entities;

use BB\Entities\ApiKey;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiKeyTest extends TestCase
{
    #[Test]
    public function serialising_an_api_key_leaves_out_its_token()
    {
        $apiKey = ApiKey::factory()->create(['api_token' => 'door-system-secret', 'description' => 'Door system']);

        $this->assertArrayNotHasKey('api_token', $apiKey->toArray());
        $this->assertStringNotContainsString('door-system-secret', $apiKey->toJson());
        $this->assertSame('Door system', $apiKey->toArray()['description']);
    }
}
