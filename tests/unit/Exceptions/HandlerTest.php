<?php

use BB\Exceptions\Handler;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    /** @var Handler|\Mockery\MockInterface */
    private $handler;

    /** @var int */
    private $telegramCallCount;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->telegramCallCount = 0;

        // Partial mock: real telegramException logic, stubbed notifyTelegram
        $this->handler = \Mockery::mock(Handler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->handler->shouldReceive('notifyTelegram')->andReturnUsing(function () {
            $this->telegramCallCount++;
        });
    }

    protected function reportExceptionToTelegram(\Throwable $e = null): void
    {
        $e = $e ?? new \RuntimeException('Test error');

        // Call telegramException directly via reflection
        $method = new \ReflectionMethod($this->handler, 'telegramException');
        $method->setAccessible(true);
        $method->invoke($this->handler, $e);
    }

    public function testFirstOccurrenceSendsTelegram()
    {
        $this->reportExceptionToTelegram();

        $this->assertEquals(1, $this->telegramCallCount);
    }

    public function testDuplicateExceptionIsSuppressed()
    {
        $e = new \RuntimeException('Duplicate error');

        $this->reportExceptionToTelegram($e);
        $this->reportExceptionToTelegram($e);
        $this->reportExceptionToTelegram($e);

        $this->assertEquals(1, $this->telegramCallCount);
    }

    public function testSendsAgainAtTenOccurrences()
    {
        $e = new \RuntimeException('Repeated error');

        for ($i = 0; $i < 10; $i++) {
            $this->reportExceptionToTelegram($e);
        }

        $this->assertEquals(2, $this->telegramCallCount);
    }

    public function testSendsAgainAtOneHundredOccurrences()
    {
        $e = new \RuntimeException('Repeated error');

        for ($i = 0; $i < 100; $i++) {
            $this->reportExceptionToTelegram($e);
        }

        // Sent at: 1, 10, 100
        $this->assertEquals(3, $this->telegramCallCount);
    }

    public function testDifferentExceptionsTrackedSeparately()
    {
        $this->reportExceptionToTelegram(new \RuntimeException('Error A'));
        $this->reportExceptionToTelegram(new \RuntimeException('Error B'));

        $this->assertEquals(2, $this->telegramCallCount);
    }

    public function testSendsAgainAfterCacheExpires()
    {
        $e = new \RuntimeException('Expiring error');

        $this->reportExceptionToTelegram($e);
        $this->reportExceptionToTelegram($e);

        $this->assertEquals(1, $this->telegramCallCount);

        // Simulate cache expiry
        Cache::flush();

        $this->reportExceptionToTelegram($e);

        $this->assertEquals(2, $this->telegramCallCount);
    }

    public function testTitleIncludesCountAtThreshold()
    {
        $capturedTitle = null;
        $this->handler = \Mockery::mock(Handler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->handler->shouldReceive('notifyTelegram')->andReturnUsing(function ($level, $title) use (&$capturedTitle) {
            $capturedTitle = $title;
        });

        $e = new \RuntimeException('Repeated error');

        for ($i = 0; $i < 10; $i++) {
            $method = new \ReflectionMethod($this->handler, 'telegramException');
            $method->setAccessible(true);
            $method->invoke($this->handler, $e);
        }

        $this->assertEquals('Error (x10)', $capturedTitle);
    }
}
