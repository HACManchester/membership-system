<?php

namespace Tests\Feature;

use BB\Entities\ApiKey;
use BB\Entities\KeyFob;
use BB\Entities\User;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Pins the parts of the keyfob export that are a contract with the door system
 * but that no other test asserts: the response headers, the exact CSV escaping,
 * and the shape of an empty export.
 *
 * KeyFobCsvTest already compares the body byte-for-byte for the simple case and
 * covers the access-lockdown filtering. What is added here is the behaviour a
 * PHP or framework upgrade is liable to perturb underneath us — fputcsv's
 * quoting and escaping in particular, whose default $escape handling PHP has
 * been moving away from.
 *
 * If a change here is deliberate, coordinate it with the acs team before
 * updating the expectations: the door controller parses this output.
 */
class KeyFobCsvContractTest extends TestCase
{
    /** @test */
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
     * fputcsv quotes a field only when it has to, escapes an embedded quote by
     * doubling it, and leaves UTF-8 alone. Semicolons are not special.
     *
     * @test
     */
    public function announce_names_are_csv_escaped_exactly_as_the_door_system_expects()
    {
        $this->activeMemberWithFob('Plain Name', 'fob_0');
        $this->activeMemberWithFob('Comma, Name', 'fob_1');
        $this->activeMemberWithFob('Quote "Bob" Name', 'fob_2');
        $this->activeMemberWithFob("Newline\nName", 'fob_3');
        $this->activeMemberWithFob("Ó'Brien — ümlaut", 'fob_4');
        $this->activeMemberWithFob('Semi;colon', 'fob_5');

        $expected = "key_id,announce_name,id\n"
            . "fob_0,\"Plain Name\",1\n"
            . "fob_1,\"Comma, Name\",2\n"
            . "fob_2,\"Quote \"\"Bob\"\" Name\",3\n"
            . "fob_3,\"Newline\nName\",4\n"
            . "fob_4,\"Ó'Brien — ümlaut\",5\n"
            . "fob_5,Semi;colon,6\n";

        $this->assertSame($expected, $this->export()->streamedContent());
    }

    /** @test */
    public function rows_are_terminated_with_a_bare_newline_and_no_carriage_return()
    {
        $this->activeMemberWithFob('Plain Name', 'fob_1');

        $body = $this->export()->streamedContent();

        $this->assertStringEndsWith("\n", $body);
        $this->assertStringNotContainsString("\r", $body);
    }

    /**
     * The controller only writes the header row when there is a first row, so an
     * empty export is a zero-byte body rather than a lone header. The door
     * system has always been fed that, so pin it rather than quietly "fix" it.
     *
     * @test
     */
    public function an_empty_export_is_an_empty_body_with_no_header_row()
    {
        $response = $this->export();

        $response->assertStatus(200);
        $this->assertSame('', $response->streamedContent());
    }

    private function activeMemberWithFob(string $announceName, string $keyId): User
    {
        $user = factory(User::class)->create([
            'active' => true,
            'announce_name' => $announceName,
        ]);

        factory(KeyFob::class)->create([
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
