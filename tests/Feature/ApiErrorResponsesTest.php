<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The door system polls api/keyfobs/csv and follows redirects. It keeps its
 * current member list on any non-200, so API errors must never be a 200 or a
 * redirect to an HTML page: they're JSON with an error status.
 */
class ApiErrorResponsesTest extends TestCase
{
    #[Test]
    public function a_missing_token_is_a_json_401_not_a_redirect_to_login()
    {
        $this->get('/api/keyfobs/csv')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function a_wrong_token_is_a_json_401()
    {
        $this->get('/api/keyfobs/csv?api_token=not-a-real-token')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function a_server_error_is_a_json_500_without_details()
    {
        config(['app.debug' => false]);
        Route::middleware('api')->get('api/probe-error', function () {
            throw new \RuntimeException('internal detail');
        });

        $this->get('/api/probe-error')
            ->assertStatus(500)
            ->assertExactJson(['message' => 'Server Error']);
    }

    #[Test]
    public function web_pages_still_redirect_guests_to_login()
    {
        $this->get('/account/1')->assertRedirect(route('login'));
    }
}
