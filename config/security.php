<?php

$appUrl = rtrim((string) env('APP_URL', 'http://momenikah.test:8080'), '/');
$mediaSources = "'self' data: blob: {$appUrl} http://*.momenikah.test:8080 https://*.momenikah.test";

return [
    'surfaces' => [
        'marketing' => [
            'frame_options' => 'DENY',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => $mediaSources,
                'media-src' => $mediaSources,
                'font-src' => "'self'",
            ],
        ],

        'app' => [
            'frame_options' => 'DENY',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => $mediaSources,
                'media-src' => $mediaSources,
                'font-src' => "'self'",
            ],
        ],

        'public' => [
            'frame_options' => 'SAMEORIGIN',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self' https://cloudflare.com",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => $mediaSources,
                'media-src' => $mediaSources,
                'font-src' => "'self'",
                'frame-src' => "'self' https://cloudflare.com",
            ],
        ],
    ],
];
