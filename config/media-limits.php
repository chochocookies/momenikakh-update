<?php

declare(strict_types=1);

/*
| Media limits. Values marked PLACEHOLDER are product decisions (DEC-04 storage, DEC-06 music)
| and must be confirmed before launch; the code never hard-codes them.
*/
return [
    'disk' => env('MEDIA_DISK', 'media'),
    'cdn_url' => env('MEDIA_CDN_URL'),

    'image' => [
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'], // no SVG, GIF, HEIC
        'max_bytes' => 10 * 1024 * 1024,   // PLACEHOLDER
        'max_width' => 12000,
        'max_height' => 12000,
        'max_pixels' => 24_000_000,        // PLACEHOLDER; decoding needs ~4 bytes per pixel of memory
        'sanitized_max_edge' => 2400,      // longest edge of the stored (clean) original
        'quality' => 85,
    ],

    // Per-collection profiles. `output` is the format of the stored clean original.
    'profiles' => [
        'cover' => ['min_width' => 1200, 'min_height' => 630, 'output' => 'webp'],   // OG image is cropped 1200x630
        'gallery' => ['min_width' => 600, 'min_height' => 400, 'output' => 'webp'],
        'photo' => ['min_width' => 400, 'min_height' => 400, 'output' => 'webp'],
        'qr' => ['min_width' => 200, 'min_height' => 200, 'output' => 'png'],        // lossless, keeps scannability
    ],

    'audio' => [
        'allowed_mimes' => ['audio/mpeg', 'audio/ogg', 'application/ogg'],
        'allowed_extensions' => ['mp3', 'ogg'],
        'max_bytes' => 6 * 1024 * 1024,    // PLACEHOLDER (DEC-06)
        'catalog_enabled' => false,        // licensed catalog stays off until DEC-06
    ],

    'orphans' => ['grace_hours' => 24],
];
