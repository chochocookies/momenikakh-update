<?php

return [
    'surfaces' => [
        'marketing' => [
            'frame_options' => 'DENY',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data:",
                'font-src' => "'self'",
            ],
        ],

        'app' => [
            'frame_options' => 'DENY',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data:",
                'font-src' => "'self'",
            ],
        ],

        'public' => [
            'frame_options' => 'SAMEORIGIN',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data:",
                'font-src' => "'self'",
            ],
        ],
    ],
];
