<?php

return [
    'marketing' => [
        'domain' => env('MARKETING_DOMAIN', 'momenikah.test'),
        'name' => 'Marketing',
    ],

    'app' => [
        'domain' => env('APP_SUBDOMAIN', 'app.momenikah.test'),
        'name' => 'Application',
    ],

    'public' => [
        'domain' => env('PUBLIC_DOMAIN', 'undangan.momenikah.test'),
        'name' => 'Public Invitation',
    ],
];
