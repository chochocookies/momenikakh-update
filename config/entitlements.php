<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Entitlements (DOM-10) and the plans of DEC-01
|--------------------------------------------------------------------------
| DEC-01 (accepted 2026-11-19):
|   - "Gratis"        : the sample invitation. Create it, fill it, preview it with every template. It cannot be published.
|   - "Paket Template": Rp 50.000, paid once per invitation, no expiry. It unlocks publishing that invitation.
| Photo and guest limits are not decided, so they stay null (= no limit).
|
| Paid plans live in the `plans` table (PlanSeeder reads this file). This config also defines the allowed ability and
| limit keys and the free-plan fallback used when no plan row exists. Plan checks happen ONLY through Entitlements.
*/
return [
    'abilities' => [
        'publish', 'premium_templates', 'custom_music', 'remove_brand', 'rsvp', 'wishes', 'gifts', 'export_guests', 'password_protect',
    ],
    'limits' => ['max_photos', 'max_guests', 'max_story_items'],

    'free' => [
        'key' => 'free',
        'name' => 'Gratis',
        'tier' => 'free',
        'price_idr' => 0,
        'duration_days' => null,
        'features' => [
            'publish' => false, 'premium_templates' => false, 'custom_music' => false, 'remove_brand' => false, 'rsvp' => true,
            'wishes' => true, 'gifts' => true, 'export_guests' => false, 'password_protect' => false,
        ],
        'limits' => ['max_photos' => null, 'max_guests' => null, 'max_story_items' => null],
    ],

    'paid' => [
        'key' => 'paket-template',
        'name' => 'Paket Template',
        'tier' => 'premium',
        'price_idr' => 50000,
        'duration_days' => null,
        'features' => [
            'publish' => true, 'premium_templates' => true, 'custom_music' => true, 'remove_brand' => false, 'rsvp' => true,
            'wishes' => true, 'gifts' => true, 'export_guests' => true, 'password_protect' => false,
        ],
        'limits' => ['max_photos' => null, 'max_guests' => null, 'max_story_items' => null],
    ],
];
