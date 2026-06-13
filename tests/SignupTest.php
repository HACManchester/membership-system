<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\BrowserKitTestCase;

class SignupTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function i_can_visit_the_signup_page()
    {
        $this->visit('/register')->see('Join');
    }


    /** @test */
    public function i_can_sign_up_successfully()
    {
        $faker = Faker\Factory::create();

        $firstName = $faker->firstName;
        $email = $faker->email;

        $this->visit('/register')
            ->see('Join')
            ->submitForm('Join Hackspace Manchester', [
                'given_name'           => $firstName,
                'family_name'          => $faker->lastName,
                'email'                => $email,
                'display_name'         => $faker->userName,
                'suppress_real_name'   => '0',
                'password'             => $faker->password(10),
                'phone'                => '07700900123',
                'address[line_1]'      => $faker->streetAddress,
                'address[postcode]'    => 'M4 7HR',
                'emergency_contact'    => $faker->name,
                'monthly_subscription' => config('membership.prices.minimum'),
                'rules_agreed'         => '1',
            ]);

        $this->seeInDatabase('users', [
            'email'      => $email,
            'given_name' => $firstName,
            'status'     => 'setting-up',
        ]);
    }
}