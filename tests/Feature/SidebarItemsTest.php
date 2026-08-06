<?php

namespace Tests\Feature;

use BB\Entities\User;
use BB\Services\SidebarItems;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarItemsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Filtering visible-gated items must re-index each group. A gap in the keys
     * makes json_encode emit a JSON object instead of an array, which blanks the
     * whole app because the frontend maps over each group.
     *
     * @test
     */
    public function every_nav_group_serialises_as_a_json_array_for_a_regular_member()
    {
        // A regular member cannot create Rooms, so that middle item is filtered
        // out of the resources group — the exact case that used to leave a gap.
        $member = factory(User::class)->create();

        $this->actingAs($member);
        $groups = (new SidebarItems)->getItems();

        foreach ($groups as $i => $group) {
            // A gapped array re-indexes under array_values; a proper list is
            // unchanged. This is exactly what decides array-vs-object in JSON.
            $this->assertSame(
                array_values($group),
                $group,
                "Nav group {$i} has non-sequential keys and would serialise as a JSON object"
            );
        }

        // Belt-and-braces: the encoded payload must contain only JSON arrays.
        $this->assertStringNotContainsString('{"0":', json_encode($groups));
    }

    /** @test */
    public function an_anonymous_visitor_gets_a_single_list_group()
    {
        $groups = (new SidebarItems)->getItems();

        $this->assertCount(1, $groups);
        $this->assertSame(array_values($groups[0]), $groups[0]);
    }
}
