<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Entitlements (DOM-10)
|--------------------------------------------------------------------------
| PLACEHOLDER VALUES: the real plan model and limits are decided in DEC-01.
| A null limit means "not decided yet" and is treated as unlimited for now.
| Paid plans live in the `plans` table; this config only defines the allowed
| keys and the free-plan fallback used when no plan row exists.
*/
return [
    'abilities' => [
        'premium_templates', 'custom_music', 'remove_brand', 'rsvp', 'wishes', 'gifts', 'export_guests', 'password_protect',
    ],
    'limits' => ['max_photos', 'max_guests', 'max_story_items'],
    'free' => [
        'key' => 'free',
        'name' => 'Gratis',
        'tier' => 'free',
        'duration_days' => null,
        'features' => [
            'premium_templates' => false, 'custom_music' => false, 'remove_brand' => false, 'rsvp' => true,
            'wishes' => true, 'gifts' => true, 'export_guests' => false, 'password_protect' => false,
        ],
        'limits' => ['max_photos' => null, 'max_guests' => null, 'max_story_items' => null],
    ],
];
