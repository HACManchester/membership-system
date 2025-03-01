<?php

use BB\Entities\User;
use BB\Jobs\DiscourseSync;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Testing\Assert;
use Tests\TestCase;

class DiscourseSyncTest extends TestCase
{
    /** @var MockHandler */
    protected $mock;

    protected $container = [];

    /** @var HandlerStack */
    protected $handlerStack;

    /** @var Client */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler([]);
        $history = Middleware::history($this->container);
        $this->handlerStack = HandlerStack::create($this->mock);
        $this->handlerStack->push($history);
        $this->client = new Client(['handler' => $this->handlerStack]);
    }

    public function testSyncsAMember()
    {
        $this->mock->append(new Response(200, [], null));

        $user = factory(User::class)->create([
            'suppress_real_name' => false,
            'display_name' => 'JDoe',
            'given_name' => 'John',
            'family_name' => 'Doe',
        ]);

        (new DiscourseSync($user))->handle($this->client);

        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];

        $this->assertEquals('/admin/users/sync_sso', $request->getUri()->getPath());

        Assert::assertArraySubset([
            'external_id' => $user->id,
            'email' => $user->email,
            'username' => 'JDoe',
            'name' => 'John Doe',
        ], $this->last_sent_payload());
    }

    public function testSuppressesRealNames()
    {
        $this->mock->append(new Response(200, [], null));

        $user = factory(User::class)->create([
            'suppress_real_name' => true,
            'display_name' => 'JDoe',
            'given_name' => 'John',
            'family_name' => 'Doe',
        ]);

        (new DiscourseSync($user))->handle($this->client);

        Assert::assertArraySubset([
            'external_id' => $user->id,
            'email' => $user->email,
            'username' => 'JDoe',
            'name' => 'JDoe',
        ], $this->last_sent_payload());
    }


    public function testGroupsActiveMembers()
    {
        $this->mock->append(new Response(200, [], null));

        $user = factory(User::class)->create([
            'active' => true,
            'status' => 'active',
        ]);

        (new DiscourseSync($user))->handle($this->client);

        Assert::assertArraySubset([
            'add_groups' => 'active_members',
            'remove_groups' => 'previous_members',
        ], $this->last_sent_payload());
    }

    public function testGroupsInactiveMembers()
    {
        $this->mock->append(new Response(200, [], null));

        $user = factory(User::class)->create([
            'active' => false,
            'status' => 'left',
        ]);

        (new DiscourseSync($user))->handle($this->client);

        Assert::assertArraySubset([
            'add_groups' => 'previous_members',
            'remove_groups' => 'active_members',
        ], $this->last_sent_payload());
    }

    protected function last_sent_payload()
    {
        $request = $this->container[0]['request'];
        $uri = $request->getUri();
        parse_str($uri->getQuery(), $query);
        parse_str(base64_decode($query['sso']), $payload);

        return $payload;
    }
}
