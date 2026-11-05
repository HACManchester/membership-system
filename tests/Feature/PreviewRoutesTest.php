<?php

namespace Tests\Feature;

use BB\Entities\User;
use Inertia\Testing\AssertableInertia;
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

        $this->actingAs($admin)->get('/email-preview')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('EmailPreview/Index')
                ->has('emails', 8)
                ->where('emails.0', ['name' => 'Payment Warning', 'url' => '/email-preview/payment-warning'])
                ->has('notifications', 8)
                ->where('notifications.0', ['name' => 'Induction Completed', 'url' => '/notification-preview/induction-completed']));
        $this->actingAs($admin)->get('/email-preview/payment-warning')->assertOk();
        $this->actingAs($admin)->get('/notification-preview/induction-completed')->assertOk();
    }

    #[Test]
    public function a_member_is_refused_the_previews()
    {
        $this->actingAs(User::factory()->create())->get('/email-preview')->assertForbidden();
    }

    #[Test]
    public function a_guest_is_sent_to_log_in()
    {
        $this->get('/email-preview')->assertRedirect(route('login'));
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
