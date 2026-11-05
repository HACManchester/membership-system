<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The legacy validators (app/Validators) throw FormValidationException. Blade
 * forms show its errors from FlashNotification's details; Inertia forms read
 * the session's errors bag, as they do for a FormRequest. Registration uses
 * UserValidator.
 */
class LegacyFormValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_blade_form_gets_the_errors_as_flash_notification_details()
    {
        $this->from(route('register'))
            ->post(route('account.store'), ['given_name' => 'Ada'])
            ->assertRedirect(route('register'))
            ->assertSessionHas('flash_notification.level', 'danger')
            ->assertSessionHas('flash_notification.details', fn ($details) => $details->has('family_name'))
            ->assertSessionHasInput('given_name', 'Ada')
            ->assertSessionDoesntHaveErrors();
    }

    #[Test]
    public function an_inertia_form_gets_the_errors_in_the_errors_bag()
    {
        $this->from(route('register'))
            ->post(route('account.store'), ['given_name' => 'Ada', 'password' => 'secret'], ['X-Inertia' => 'true'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['family_name', 'email', 'rules_agreed'])
            ->assertSessionDoesntHaveErrors('given_name')
            ->assertSessionHasInput('given_name', 'Ada')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('flash_notification.message');
    }
}
