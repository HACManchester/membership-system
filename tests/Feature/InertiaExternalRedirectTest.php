<?php

namespace Tests\Feature;

use BB\Entities\Room;
use BB\Entities\User;
use BB\Helpers\GoCardlessHelper;
use BB\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * An Inertia visit is an XHR, which can't follow a redirect to another site,
 * so HandleInertiaRequests turns one into a 409 with X-Inertia-Location and the
 * browser goes there itself. Setting up a Direct Debit ends at GoCardless.
 */
class InertiaExternalRedirectTest extends TestCase
{
    private const GOCARDLESS_URL = 'https://pay-sandbox.gocardless.com/flow/RE123';

    protected function setUp(): void
    {
        parent::setUp();

        $goCardless = $this->createStub(GoCardlessHelper::class);
        $goCardless->method('newPreAuthUrl')->willReturn(self::GOCARDLESS_URL);
        $goCardless->method('cancelSubscription')->willReturn((object) ['status' => 'cancelled']);
        $this->app->instance(GoCardlessHelper::class, $goCardless);
    }

    #[Test]
    public function an_inertia_visit_is_sent_to_gocardless_by_the_browser()
    {
        $member = $this->member();

        $this->actingAs($member)
            ->get(route('account.subscription.create', $member->id), $this->inertiaHeaders())
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', self::GOCARDLESS_URL);
    }

    #[Test]
    public function an_inertia_form_is_sent_to_gocardless_by_the_browser()
    {
        $member = $this->member(['payment_method' => 'gocardless', 'subscription_id' => 'SB_LEGACY_1']);

        $this->actingAs($member)
            ->post(route('account.payment.gocardless-migrate'), [], $this->inertiaHeaders())
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', self::GOCARDLESS_URL);

        $this->assertSame('', $member->fresh()->subscription_id);
    }

    #[Test]
    public function a_prefetch_still_gets_the_redirect()
    {
        $member = $this->member();

        $this->actingAs($member)
            ->get(route('account.subscription.create', $member->id), $this->inertiaHeaders() + ['Purpose' => 'prefetch'])
            ->assertRedirect(self::GOCARDLESS_URL)
            ->assertHeaderMissing('X-Inertia-Location');
    }

    #[Test]
    public function the_same_host_on_another_port_is_another_site()
    {
        Route::middleware('web')->get('_test/away', fn (Request $request) => redirect()->away($request->query('to')));
        $app = Request::create(url('/'));
        $samePort = "http://{$app->getHost()}:{$app->getPort()}/account";
        $otherPort = "http://{$app->getHost()}:" . ($app->getPort() + 1) . '/';

        $this->get('_test/away?to=' . urlencode($otherPort), $this->inertiaHeaders())
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', $otherPort);

        $this->get('_test/away?to=' . urlencode($samePort), $this->inertiaHeaders())
            ->assertRedirect($samePort)
            ->assertHeaderMissing('X-Inertia-Location');
    }

    #[Test]
    public function a_normal_request_still_gets_a_redirect()
    {
        $member = $this->member();

        $this->actingAs($member)
            ->get(route('account.subscription.create', $member->id))
            ->assertRedirect(self::GOCARDLESS_URL)
            ->assertHeaderMissing('X-Inertia-Location');
    }

    #[Test]
    public function an_inertia_redirect_within_the_app_is_unchanged()
    {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->create(['slug' => 'woodwork']);

        $this->actingAs($admin)
            ->put(route('room.update', $room), ['name' => 'Woodwork', 'slug' => 'woodwork'], $this->inertiaHeaders())
            ->assertStatus(303)
            ->assertRedirect(route('room.show', $room))
            ->assertHeaderMissing('X-Inertia-Location');
    }

    /**
     * @return array<string, string>
     */
    private function inertiaHeaders(): array
    {
        return array_filter([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(Request::create('/')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function member(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'status' => 'active',
            'active' => true,
            'monthly_subscription' => 22,
        ], $attributes));
    }
}
