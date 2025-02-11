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
                'title' => '🔨 Supporter',
                'description' => 'Your contribution at this level helps us cover essential costs like rent, utilities, and insurance, ensuring the makerspace remains operational.',
            ],
            'standard' => [
                'value_in_pence' => 2000,
                'title' => '⚙️ Sustainer',
                'description' => 'Your support helps us maintain and upgrade our equipment, and provide a welcoming space for makers to connect and create.',
            ],
            'bonus' => [
                'value_in_pence' => 2500,
                'title' => '👑 Champion',
                'description' => 'Become a champion for making! Your generous contribution directly fuels the growth of our makerspace, allowing us to invest in new equipment and enhance our community.',
            ],
        ]
    ]
];