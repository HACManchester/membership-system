<?php

// Laravel Boost (dev only). Only the keys we change; the package's defaults
// fill in the rest.
return [
    'guidelines' => [
        // We self-host (membership-deployment), so the Laravel Cloud guidance doesn't apply
        'exclude' => ['deployments'],
    ],
];
