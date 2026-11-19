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

        // Invitations may not be framed. Only the template preview frame (/preview/{template}/frame) is allowed to be
        // embedded by its own wrapper page; App\Http\Middleware\PreviewHeaders sets that exception on those routes.
        'public' => [
            'frame_options' => 'DENY',
            'csp' => [
                'default-src' => "'self'",
                'script-src' => "'self'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data:",
                'font-src' => "'self'",
                'frame-ancestors' => "'none'",
            ],
        ],
    ],
];
