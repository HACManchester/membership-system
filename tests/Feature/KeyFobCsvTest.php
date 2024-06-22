<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;

class KeyFobCsvTest extends TestCase
{
    public function testKeyFobsCsvList()
    {
        $token = \BB\Entities\ApiKey::create(['api_token' => Str::random(), 'description' => 'Test Key', 'active' => true]);

        $user_1 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => 'User One']);
        $user_2 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => null]);
        $user_3 = factory('BB\Entities\User')->create(['active' => false, 'announce_name' => 'Inactive User']);
        $user_4 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => 'Inactive Fob']);
        $user_5 = factory('BB\Entities\User')->create(['active' => true, 'announce_name' => 'No fob']);

        $keyfob_1 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_1', 'active' => true, 'user_id' => $user_1->id]);
        $keyfob_2 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_2', 'active' => true, 'user_id' => $user_2->id]);
        $keyfob_3 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_3', 'active' => true, 'user_id' => $user_3->id]);
        $keyfob_4 = factory('BB\Entities\KeyFob')->create(['key_id' => 'keyfob_4', 'active' => false, 'user_id' => $user_4->id]);

        $response = $this->get('/api/keyfobs/csv?api_token=' . $token->api_token);

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $expectedContent = <<<'CSV'
key_id,announce_name,id
keyfob_1,"User One",1
keyfob_2,,2

CSV;
        $this->assertEquals($expectedContent, $content);
    }
}
