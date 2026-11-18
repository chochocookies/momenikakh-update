<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Staging Environment Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings specific to the staging
    | environment. Values here override the defaults in config/app.php
    | and other configuration files when APP_ENV=staging.
    |
    */

    // Application security in staging
    'security' => [
        // Enable debug bar for development but not in staging
        'debug_bar' => false,
        // Enable query logging for debugging
        'query_logging' => true,
        // Log slow queries over this threshold (milliseconds)
        'slow_query_threshold' => 1000,
    ],

    // Staging-specific rate limits (less restrictive than production)
    'rate_limits' => [
        'login' => [
            'attempts_per_minute' => 10, // Double the production limit
            'lockout_duration_seconds' => 60,
        ],
        'registration' => [
            'attempts_per_10_minutes' => 5, // Double the production limit
            'lockout_duration_seconds' => 600,
        ],
    ],

    // Email configuration for staging
    'mail' => [
        // Use mailtrap or similar for staging
        'driver' => env('MAIL_MAILER', 'log'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@staging.momenikah.test'),
        'from_name' => env('MAIL_FROM_NAME', 'Momenikah Staging'),
    ],

    // Cache configuration
    'cache' => [
        'prefix' => env('CACHE_PREFIX', 'momenikah_staging_'),
    ],

    // Queue configuration
    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'redis'),
        'batching' => [
            'database' => env('DB_CONNECTION'),
            'table' => 'job_batches',
        ],
    ],

    // Storage configuration
    'storage' => [
        'public_disk' => env('FILESYSTEM_DISK', 's3'),
        'disk' => env('FILESYSTEM_DISK', 's3'),
        'url' => env('APP_STORAGE_URL', 'https://staging.momenikah.test/storage'),
    ],

    // Sentry error reporting for staging
    'sentry' => [
        'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),
        'environment' => 'staging',
    ],

    // Security headers (more relaxed in staging)
    'security_headers' => [
        // Enable HSTS in staging (with shorter duration)
        'hsts_max_age' => 86400, // 1 day (vs 1 year in production)
        // Allow framing for testing
        'x_frame_options' => 'SAMEORIGIN',
    ],

    // Debug settings
    'debug' => [
        // Enable Laravel Telescope in staging
        'telescope' => env('TELESCOPE_ENABLED', true),
        // Enable Horizon dashboard
        'horizon' => env('HORIZON_ENABLED', true),
        // Log all queries
        'log_queries' => true,
    ],

    // Feature flags for staging
    'features' => [
        // Enable experimental features
        'experimental_features' => env('EXPERIMENTAL_FEATURES', true),
        // Enable developer tools
        'developer_tools' => env('DEVELOPER_TOOLS', true),
    ],
];
