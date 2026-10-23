<?php

namespace Tests\Unit\Validators;

use BB\Entities\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Laravel's default `email` rule accepts addresses carrying CR/LF
 * (CVE-2026-48019 / PKSA-3r5d-mb8f-1qw9), which lets a submitted address smuggle
 * extra headers into any mail sent to it. The fix only shipped in Laravel
 * 12.60 / 13.10, so we reject CR/LF ourselves in BB\Validators\CustomValidator —
 * which every validator in the app is built through, so it covers every
 * `email` rule, present and future.
 */
class EmailCrlfValidationTest extends TestCase
{
    /**
     * @dataProvider crlfAddresses
     * @test
     */
    public function the_email_rule_rejects_addresses_containing_cr_or_lf($address)
    {
        $validator = Validator::make(['email' => $address], ['email' => 'required|email']);

        $this->assertTrue($validator->fails(), 'accepted: ' . json_encode($address));
    }

    public static function crlfAddresses()
    {
        return [
            'header injection'   => ["member@example.com\r\nBcc: someone@example.com"],
            'trailing CRLF'      => ["member@example.com\r\n"],
            'trailing LF'        => ["member@example.com\n"],
            'bare CR'            => ["member@example.com\rBcc: someone@example.com"],
            'quoted local part'  => ["\"member\r\nBcc: someone@example.com\"@example.com"],
        ];
    }

    /**
     * @dataProvider validAddresses
     * @test
     */
    public function ordinary_addresses_still_pass($address)
    {
        $validator = Validator::make(['email' => $address], ['email' => 'required|email']);

        $this->assertFalse($validator->fails(), 'rejected: ' . $address);
    }

    public static function validAddresses()
    {
        return [
            ['member@example.com'],
            ['first.last+tag@sub.example.co.uk'],
            ['o\'brien@example.ie'],
        ];
    }

    /**
     * Through the real signup endpoint, which anyone can reach. The payload is
     * otherwise valid (it mirrors SignupTest), so the email is the only thing
     * that can stop the account being created.
     *
     * @test
     */
    public function signup_rejects_an_email_carrying_crlf()
    {
        $payload = [
            'given_name'           => 'Mallory',
            'family_name'          => 'Example',
            'display_name'         => 'mallory',
            'online_only'          => '0',
            'suppress_real_name'   => '0',
            'password'             => 'a-long-enough-password',
            'phone'                => '07700900123',
            'address'              => ['line_1' => '1 Test Street', 'postcode' => 'M4 7HR'],
            'emergency_contact'    => 'Someone Else',
            'monthly_subscription' => config('membership.prices.minimum'),
            'rules_agreed'         => '1',
        ];

        $this->post(route('account.store'), $payload + [
            'email' => "\"mallory\r\nBcc: someone@example.com\"@example.com",
        ]);
        $this->assertEquals(0, User::where('given_name', 'Mallory')->count());

        // Control: the same payload with a clean address does register
        $this->post(route('account.store'), $payload + ['email' => 'mallory@example.com']);
        $this->assertEquals(1, User::where('given_name', 'Mallory')->count());
    }
}
