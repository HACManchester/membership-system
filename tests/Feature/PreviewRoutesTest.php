<?php

namespace Tests\Feature;

use BB\Entities\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The email and notification previews build their sample members with model
 * factories, which need Faker, a dev-only dependency. They exist everywhere
 * but production.
 */
class PreviewRoutesTest extends TestCase
{
    protected function tearDown(): void
    {
        $_SERVER['APP_ENV'] = 'testing';

        parent::tearDown();
    }

    #[Test]
    public function admin_can_open_the_previews_outside_production()
    {
        $this->artisan('migrate');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/email-preview')->assertOk();
        $this->actingAs($admin)->get('/email-preview/payment-warning')->assertOk();
        $this->actingAs($admin)->get('/notification-preview/induction-completed')->assertOk();
    }

    #[Test]
    public function previews_are_not_found_in_production()
    {
        $this->bootInProduction();
        $admin = User::factory()->admin()->create();

        // HTTPS, because production redirects plain HTTP before routing
        $this->actingAs($admin)->get('https://localhost/email-preview')->assertNotFound();
        $this->actingAs($admin)->get('https://localhost/email-preview/payment-warning')->assertNotFound();
        $this->actingAs($admin)->get('https://localhost/notification-preview/induction-completed')->assertNotFound();
    }

    /**
     * Routes are registered while the app boots, so production needs a fresh
     * app. RefreshDatabase's shared in-memory connection can't follow it there,
     * so each test migrates its own app's database.
     */
    private function bootInProduction(): void
    {
        $_SERVER['APP_ENV'] = 'production';
        $this->refreshApplication();
        $this->artisan('migrate', ['--force' => true]);

        $this->assertTrue($this->app->isProduction());
    }
}
