<?php

/*
 * Overrides for inertia-laravel's own config (vendor/inertiajs/inertia-laravel/config/inertia.php),
 * which fills in every key not set here.
 */
return [
    // inertia-laravel 3 looks for pages in resources/js/pages. Ours are in js/Pages, which
    // matters on a case-sensitive filesystem: assertInertia() checks that the page exists.
    'pages' => [
        'ensure_pages_exist' => false,
        'paths' => [
            resource_path('js/Pages'),
        ],
        'extensions' => [
            'tsx',
        ],
    ],
];
