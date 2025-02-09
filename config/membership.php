<?php

return [
    'prices' => [
        // The minimum price we'll accept for online subscriptions, in pence
        'minimum' => 1500,

        // The recommended price, in pence
        'recommended' => 2000,

        'options' => [
            'minimum' => [
                'value_in_pence' => 1500,
                'title' => 'Low income',
                'description' => 'Our minimum membership amount is designed to be as accessible as possible. If you are unable to afford this, please get in touch with us to discuss alternative options.',
            ],
            'standard' => [
                'value_in_pence' => 2000,
                'title' => 'Standard',
                'description' => 'This is our standard recommended membership amount. Your support helps us maintain and upgrade our equipment, and provide a welcoming space for makers to connect and create.',
            ],
            'bonus' => [
                'value_in_pence' => 2500,
                'title' => 'Supporters / Business',
                'description' => "For those who believe in the makerspace's goals and are able to give a little extra to support us.\n\nBusinesses using our facilities are asked to contribute a minimum of this level to help sustain the makerspace.",
            ],
        ]
    ]
];