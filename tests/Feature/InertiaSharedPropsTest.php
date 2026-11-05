<?php

namespace Tests\Feature;

use BB\Entities\Equipment;
use BB\Entities\Room;
use BB\Entities\User;
use BB\Http\Middleware\HandleInertiaRequests;
use BB\FlashNotification\FlashNotificationFacade as FlashNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The props every Inertia page gets from HandleInertiaRequests: what a form
 * shows after a failed or successful submission.
 *
 * inertia-laravel reads validation errors when the middleware runs, not when
 * the page renders, so the middleware has to come after the session starts.
 * Registered globally, it saw no session and every form silently lost its
 * validation messages, while assertSessionHasErrors() still passed.
 */
class InertiaSharedPropsTest extends TestCase
{
    #[Test]
    public function validation_errors_reach_the_page_after_a_failed_submission()
    {
        $admin = User::factory()->admin()->create();

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
        $admin = User::factory()->admin()->create();

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
    public function an_inertia_validation_failure_uses_only_the_errors_bag()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('room.create'))
            ->post(route('room.store'), ['name' => ''], ['X-Inertia' => 'true'])
            ->assertRedirect(route('room.create'))
            ->assertSessionHasErrors(['name' => 'The name field is required.'])
            ->assertSessionMissing('flash_notification.message')
            ->assertSessionMissing('flash_notification.details');
    }

    #[Test]
    public function a_flashed_message_reaches_the_page_after_a_submission()
    {
        $member = User::factory()->create(['status' => 'active', 'active' => true]);
        $equipment = Equipment::factory()->create(['induction_category' => null]);

        $this->actingAs($member)
            ->followingRedirects()
            ->post(route('equipment_training.create', $equipment))
            ->assertInertia(function ($page) {
                $page->component('Equipment/Show')
                    ->hasFlash('notification', [
                        'message' => 'This equipment does not have an induction configured.',
                        'severity' => 'error',
                    ])
                    ->missing('flash');
            });
    }

    #[Test]
    public function a_flash_notification_success_reaches_the_page_after_a_submission()
    {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($admin)
            ->followingRedirects()
            ->put(route('room.update', $room), ['name' => 'Woodwork', 'slug' => 'old-name'])
            ->assertInertia(function ($page) {
                $page->component('Rooms/Show')
                    ->hasFlash('notification', [
                        'message' => 'Room, Woodwork, updated successfully.',
                        'severity' => 'success',
                    ]);
            });
    }

    #[Test]
    public function a_flash_notification_error_reaches_the_page_after_a_submission()
    {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->create(['name' => 'Woodwork']);
        Equipment::factory()->create(['room_id' => $room->id]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->delete(route('room.destroy', $room))
            ->assertInertia(function ($page) {
                $page->component('Rooms/Show')
                    ->hasFlash('notification', [
                        'message' => "Can't delete Woodwork while equipment is still assigned to it. Reassign that equipment to another room first.",
                        'severity' => 'error',
                    ]);
            });
    }

    #[Test]
    public function a_flash_notification_reaches_the_page_after_a_redirecting_visit()
    {
        $member = User::factory()->create(['status' => 'active', 'active' => true]);
        $former = User::factory()->create(['status' => 'left', 'active' => false]);

        $this->actingAs($member)
            ->followingRedirects()
            ->get(route('members.show', $former))
            ->assertInertia(function ($page) {
                $page->component('Members/Index')
                    ->hasFlash('notification', [
                        'message' => "This user's profile is no longer available as they are not an active member.",
                        'severity' => 'error',
                    ]);
            });
    }

    #[Test]
    public function an_inertia_validation_failure_flashes_no_snackbar_message()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('room.create'))
            ->post(route('room.store'), ['name' => ''], ['X-Inertia' => 'true']);

        $version = app(HandleInertiaRequests::class)->version(Request::create('/'));

        $this->actingAs($admin)
            ->get(route('room.create'), array_filter([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => $version,
            ]))
            ->assertJsonPath('props.errors.name', 'The name field is required.')
            ->assertJsonMissingPath('flash');
    }

    #[Test]
    public function a_flash_notification_keeps_its_info_and_warning_levels()
    {
        Route::middleware('web')->get('_test/flash/{level}', function (string $level) {
            FlashNotification::{$level}("A {$level} message");

            return redirect('component-zoo');
        });

        foreach (['info', 'warning'] as $level) {
            $this->followingRedirects()
                ->get("_test/flash/{$level}")
                ->assertInertia(fn ($page) => $page->hasFlash('notification', [
                    'message' => "A {$level} message",
                    'severity' => $level,
                ]));
        }
    }

    /**
     * A Blade page shows the message itself, so the next Inertia page mustn't
     * show it again.
     */
    #[Test]
    public function a_message_shown_on_a_blade_page_isnt_shown_again()
    {
        Route::middleware('web')->get('_test/flash-to-blade', function () {
            FlashNotification::success('Shown on the Blade page');

            return redirect()->route('register');
        });

        $this->followingRedirects()
            ->get('_test/flash-to-blade')
            ->assertOk()
            ->assertSee('Shown on the Blade page');

        $this->get('component-zoo')
            ->assertInertia(fn ($page) => $page->missingFlash('notification'));
    }
}
