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
     * They're the early warning for each PHP and package upgrade, so wrap
     * Laravel's error handler and turn every deprecation back into a failure:
     * our own code's, and those raised inside vendor/, which usually mean a
     * package needs upgrading or we're calling an API it has deprecated.
     * Silenced ones count too, because libraries (Faker, Symfony) report API
     * misuse through a silenced trigger_error(). Only known vendor
     * deprecations (see knownVendorDeprecations()), and every other kind of
     * error, pass through to Laravel unchanged.
     */
    private function failOnDeprecations(): void
    {
        $previous = set_error_handler(function ($level, $message, $file = '', $line = 0) use (&$previous) {
            if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED], true) && !$this->isKnownVendorDeprecation($message, $file)) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }

            return $previous ? $previous($level, $message, $file, $line) : false;
        });
    }

    /**
     * Deprecations raised inside dependencies that we know about and can't fix
     * from here. Each entry is a vendor path prefix and a message prefix. Comment
     * each one with what removes it, and delete it when that happens. None today.
     *
     * @return list<array{path: string, message: string}>
     */
    protected function knownVendorDeprecations(): array
    {
        return [];
    }

    private function isKnownVendorDeprecation(string $message, string $file): bool
    {
        foreach ($this->knownVendorDeprecations() as $known) {
            if (str_starts_with((string) realpath($file), base_path($known['path']))
                && str_starts_with($message, $known['message'])) {
                return true;
            }
        }

        return false;
    }
}
