<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->failOnDeprecations();
    }

    protected function tearDown(): void
    {
        restore_error_handler();

        parent::tearDown();
    }

    /**
     * Since Laravel 8.x, PHP deprecations no longer throw: they go to the
     * `deprecations` log channel, and under PHPUnit they're dropped entirely.
     * They're the early warning for each PHP upgrade, so wrap Laravel's error
     * handler and turn them back into failures. Everything else is passed
     * through to Laravel unchanged.
     */
    private function failOnDeprecations()
    {
        $previous = set_error_handler(function ($level, $message, $file = '', $line = 0) use (&$previous) {
            // Respect `@`, as Laravel 7's handler did: libraries such as Faker raise
            // opt-in deprecation notices through a silenced trigger_error()
            if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED], true) && (error_reporting() & $level)) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }

            return $previous ? $previous($level, $message, $file, $line) : false;
        });
    }
}
