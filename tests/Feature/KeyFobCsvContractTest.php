<?php

namespace Tests\Feature;

use BB\Entities\ApiKey;
use BB\Entities\KeyFob;
use BB\Entities\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pins the keyfob export the door system (conorriches/doorbotjs) polls.
 *
 * What it depends on, from its code: a 200 with valid RFC 4180 CSV that its
 * csv-parse accepts, columns in the order key_id, announce_name, id (read by
 * position), and a non-empty body (an empty one is ignored and the previous
 * list kept, so the header row is always sent). One row it can't parse discards the whole update. It doesn't
 * read the response headers, the header row's names or the line endings.
 *
 * The exact bytes are still pinned here so any change is deliberate; check
 * with the acs team before changing what the door system reads.
 */
class KeyFobCsvContractTest extends TestCase
{
    #[Test]
    public function the_export_is_served_as_a_utf8_csv_attachment()
    {
        $this->activeMemberWithFob('Plain Name', 'fob_1');

        $response = $this->export();

        $response->assertStatus(200);
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        // Avoids assertRegExp, which PHPUnit 9 renames.
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment; filename=keyfobs_', $disposition);
        $this->assertStringEndsWith('.csv', $disposition);
    }

    /**
     * RFC 4180: a field is quoted when it has to be, an embedded quote is
     * doubled, and a backslash is an ordinary character. PHP's old "\\" escape
     * wrote a name containing \" as "Back\"slash", which csv-parse rejects,
     * stalling the door's whole member list. UTF-8 and semicolons pass through.
     */
    #[Test]
    public function announce_names_are_csv_escaped_exactly_as_the_door_system_expects()
    {
        $this->activeMemberWithFob('Plain Name', 'fob_0');
        $this->activeMemberWithFob('Comma, Name', 'fob_1');
        $this->activeMemberWithFob('Quote "Bob" Name', 'fob_2');
        $this->activeMemberWithFob("Newline\nName", 'fob_3');
        $this->activeMemberWithFob("Ó'Brien — ümlaut", 'fob_4');
        $this->activeMemberWithFob('Semi;colon', 'fob_5');
        $this->activeMemberWithFob('Back\\"slash', 'fob_6');
        $this->activeMemberWithFob('Trailing\\', 'fob_7');

        $expected = "key_id,announce_name,id\n"
            . "fob_0,\"Plain Name\",1\n"
            . "fob_1,\"Comma, Name\",2\n"
            . "fob_2,\"Quote \"\"Bob\"\" Name\",3\n"
            . "fob_3,\"Newline\nName\",4\n"
            . "fob_4,\"Ó'Brien — ümlaut\",5\n"
            . "fob_5,Semi;colon,6\n"
            . "fob_6,\"Back\\\"\"slash\",7\n"
            . "fob_7,Trailing\\,8\n";

        $this->assertSame($expected, $this->export()->streamedContent());
    }

    #[Test]
    public function rows_are_terminated_with_a_bare_newline_and_no_carriage_return()
    {
        $this->activeMemberWithFob('Plain Name', 'fob_1');

        $body = $this->export()->streamedContent();

        $this->assertStringEndsWith("\n", $body);
        $this->assertStringNotContainsString("\r", $body);
    }

    /**
     * The header row is always sent. The door system ignores a zero-byte body and
     * keeps its previous list, so with no fobs to export it would go on admitting
     * everyone it last knew about. A header-only list admits nobody.
     */
    #[Test]
    public function an_empty_export_is_the_header_row_alone()
    {
        $response = $this->export();

        $response->assertStatus(200);
        $this->assertSame("key_id,announce_name,id\n", $response->streamedContent());
    }

    private function activeMemberWithFob(string $announceName, string $keyId): User
    {
        $user = User::factory()->create([
            'active' => true,
            'announce_name' => $announceName,
        ]);

        KeyFob::factory()->create([
            'key_id' => $keyId,
            'active' => true,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function export()
    {
        $token = ApiKey::create([
            'api_token' => Str::random(),
            'description' => 'Contract test key',
            'active' => true,
        ]);

        return $this->get('/api/keyfobs/csv?api_token=' . $token->api_token);
    }
}
