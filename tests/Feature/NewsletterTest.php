<?php

namespace Tests\Feature;

use BB\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private function lapsedMember(array $attributes): User
    {
        return User::factory()->create([
            'status' => 'left',
            'active' => false,
            'subscription_expires' => now()->subMonth(),
            ...$attributes,
        ]);
    }

    #[Test]
    public function recipients_are_opted_in_members_who_are_active_or_recently_lapsed()
    {
        User::factory()->create(['email' => 'active-in@example.test', 'newsletter' => true]);
        User::factory()->create(['email' => 'active-out@example.test', 'newsletter' => false]);
        $this->lapsedMember(['email' => 'lapsed-in@example.test', 'newsletter' => true]);
        $this->lapsedMember(['email' => 'lapsed-out@example.test', 'newsletter' => false]);
        $this->lapsedMember([
            'email' => 'long-gone@example.test',
            'newsletter' => true,
            'subscription_expires' => now()->subYear(),
        ]);
        $admin = User::factory()->admin()->create(['email' => 'admin@example.test', 'newsletter' => false]);

        $this->actingAs($admin)
            ->get(route('newsletter'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Newsletter/Index')
                ->where('newsletterRecipientEmails', ['active-in@example.test', 'lapsed-in@example.test']));
    }
}
