<?php

use BB\Helpers\TelegramHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TelegramHelperTest extends TestCase
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

        Config::set('telegram.bot_key', 'test-bot-key');
        Config::set('telegram.bot_chat', 'test-chat-id');

        $this->mock = new MockHandler([]);

        $history = Middleware::history($this->container);
        $this->handlerStack = HandlerStack::create($this->mock);
        $this->handlerStack->push($history);

        $this->client = new Client(['handler' => $this->handlerStack]);
    }

    protected function tearDown(): void
    {
        Config::set('telegram.bot_key', null);
        Config::set('telegram.bot_chat', null);
    }

    public function notifyDataProvider()
    {
        return [
            [1, 'I am level "job"', '%E2%8F%B0++%5Btest-id%5D++I am level "job"'],
            [2, 'I am level "log"', '%F0%9F%93%9C++%5Btest-id%5D++I am level "log"'],
            [3, 'I am level "render"', '%F0%9F%91%80++%5Btest-id%5D++I am level "render"'],
            [4, 'I am level "error"', '%F0%9F%9B%91++%5Btest-id%5D++I am level "error"'],
            [5, 'I am level "warning"', '%E2%9A%A0%EF%B8%8F++%5Btest-id%5D++I am level "warning"'],
            [99, 'I am an unrecognised level', '%E2%84%B9%EF%B8%8F++%5Btest-id%5D++I am an unrecognised level'],
        ];
    }

    /**
     * @dataProvider notifyDataProvider
     */
    public function testNotify($level, $message, $encodedTextParam)
    {
        $this->mock->reset();
        $this->mock->append(new Response(200, [], null));


        $telegramHelper = new TelegramHelper('test-id', $this->client);
        $telegramHelper->notify($level, $message);

        $this->assertCount(1, $this->container);

        /** @var Request */
        $request = $this->container[0]['request'];
        $params = [];
        parse_str($request->getUri()->getQuery(), $params);

        $this->assertEquals($encodedTextParam, $params['text']);
    }
}
