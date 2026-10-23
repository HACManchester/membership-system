<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    #[Test]
    public function the_homepage_loads()
    {
        // The landing page is now an Inertia page; its content renders client-side,
        // so assert the Inertia component rather than server-rendered markup.
        $this->get('/')
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $page->component('Home')->has('urls');
            });
    }
}
