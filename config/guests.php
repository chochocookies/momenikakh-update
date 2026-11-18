<?php

declare(strict_types=1);

return [
    /*
    | Base URL of the public (invitation) surface, no trailing slash. Guest links are built from it.
    | Falls back to config('surfaces.public.url') when that exists.
    */
    'public_base_url' => env('PUBLIC_BASE_URL', env('APP_URL')),

    'import' => [
        // PLACEHOLDERS: confirm the real limits with the plan model (DEC-01).
        'max_rows' => 500,
        'max_bytes' => 512 * 1024,
        'allowed_mimes' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
        'ttl_minutes' => 60, // how long a previewed file may wait for confirmation
    ],

    'name_max' => 100,
    'group_max' => 60,

    'share' => [
        'message_max' => 900, // keeps the wa.me URL below common length limits
        'default_message' => "Halo {nama},\n\nKami mengundang Anda untuk hadir di acara \"{judul}\". Detailnya ada di undangan digital kami:\n{tautan}\n\nTerima kasih.",
    ],

    'export' => [
        'delimiter' => ',',        // use ';' for Excel with Indonesian regional settings
        'timezone_fallback' => 'Asia/Jakarta',
    ],
];
