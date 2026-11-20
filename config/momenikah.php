<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Momenikah (FND-10)
|--------------------------------------------------------------------------
| Product-wide settings that are not a secret. Hosts live in config/surfaces.php (the base URL of shared invitation
| links is guests.public_base_url, from PUBLIC_BASE_URL), plans and limits in
| config/entitlements.php (DEC-01), template paths in config/templates.php. Nothing here may hold a key or a token:
| those stay in .env and are read only by the config file of the service that needs them.
|
| Environment keys read by this file (all optional, with safe defaults):
|   FEATURE_PAYMENTS      true once checkout (BIL-01/02) works; until then pages say that payment opens soon
*/
return [
    'name' => 'Momenikah',

    'tagline' => 'Satu link untuk semua momen nikahmu.',

    'features' => [
        'payments' => (bool) env('FEATURE_PAYMENTS', false),
    ],
];
