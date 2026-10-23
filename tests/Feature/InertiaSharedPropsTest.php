<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\User;
use BB\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The props every Inertia page gets from HandleInertiaRequests: what a form
 * shows after a failed or successful submission.
 *
 * inertia-laravel 2 reads validation errors when the middleware runs, not when
 * the page renders, so the middleware has to come after the session starts.
 * Registered globally, it saw no session and every form silently lost its
 * validation messages, while assertSessionHasErrors() still passed.
 */
class InertiaSharedPropsTest extends TestCase
{
    #[Test]
    public function validation_errors_reach_the_page_after_a_failed_submission()
    {
        $admin = factory(User::class)->state('admin')->create();

        $this->actingAs($admin)
            ->from(route('room.create'))
            ->followingRedirects()
            ->post(route('room.store'), ['name' => ''])
            ->assertInertia(function ($page) {
                $page->component('Rooms/Create')
                    ->where('errors.name', 'The name field is required.');
            });
    }

    #[Test]
    public function validation_errors_reach_a_client_side_visit()
    {
        $admin = factory(User::class)->state('admin')->create();

        $this->actingAs($admin)
            ->from(route('room.create'))
            ->post(route('room.store'), ['name' => ''], ['X-Inertia' => 'true'])
            ->assertRedirect(route('room.create'));

        $version = app(HandleInertiaRequests::class)->version(Request::create('/'));

        $this->actingAs($admin)
            ->get(route('room.create'), array_filter([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => $version,
            ]))
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('props.errors.name', 'The name field is required.');
    }

    #[Test]
    public function a_flashed_message_reaches_the_page_after_a_submission()
    {
        $member = factory(User::class)->create(['status' => 'active', 'active' => true]);
        $equipment = factory(Equipment::class)->create(['induction_category' => null]);

        $this->actingAs($member)
            ->followingRedirects()
            ->post(route('equipment_training.create', $equipment))
            ->assertInertia(function ($page) {
                $page->component('Equipment/Show')
                    ->where('flash.error', 'This equipment does not have an induction configured.');
            });
    }
}
