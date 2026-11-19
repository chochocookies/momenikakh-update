<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Server Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file defines server-specific settings for different
    | environments including staging, production, and local development.
    |
    */

    'staging' => [
        'name' => 'Staging Server',
        'host' => env('STAGING_HOST', 'staging.momenikah.test'),
        'ssh' => [
            'user' => env('STAGING_SSH_USER', 'deploy'),
            'host' => env('STAGING_SSH_HOST', 'staging.momenikah.test'),
            'port' => env('STAGING_SSH_PORT', 22),
            'key' => env('STAGING_SSH_KEY', '~/.ssh/id_rsa'),
        ],
        'web_server' => [
            'type' => 'nginx',
            'config_path' => '/etc/nginx/sites-available/momenikah-staging',
            'site_enabled' => '/etc/nginx/sites-enabled/momenikah-staging',
            'service' => 'nginx',
        ],
        'php_fpm' => [
            'service' => 'php8.3-fpm',
            'pool' => 'momenikah',
        ],
        'database' => [
            'host' => env('STAGING_DB_HOST', 'staging-db.momenikah.internal'),
            'port' => env('STAGING_DB_PORT', 3306),
            'database' => env('STAGING_DB_DATABASE', 'momenikah_staging'),
            'username' => env('STAGING_DB_USERNAME', 'momenikah_staging'),
        ],
        'redis' => [
            'host' => env('STAGING_REDIS_HOST', 'staging-redis.momenikah.internal'),
            'port' => env('STAGING_REDIS_PORT', 6379),
        ],
        'storage' => [
            'disk' => env('STAGING_STORAGE_DISK', 's3'),
            'bucket' => env('STAGING_STORAGE_BUCKET', 'momenikah-staging'),
            'region' => env('STAGING_STORAGE_REGION', 'ap-southeast-1'),
        ],
        'backup' => [
            'enabled' => true,
            'schedule' => '0 2 * * *', // Daily at 02:00 UTC
            'retention_days' => 14,
            'directory' => '/var/backups/momenikah/mysql',
        ],
        'monitoring' => [
            'sentry' => [
                'enabled' => true,
                'environment' => 'staging',
            ],
        ],
    ],

    'production' => [
        'name' => 'Production Server',
        'host' => env('PRODUCTION_HOST', 'momenikah.test'),
        'ssh' => [
            'user' => env('PRODUCTION_SSH_USER', 'deploy'),
            'host' => env('PRODUCTION_SSH_HOST', 'momenikah.test'),
            'port' => env('PRODUCTION_SSH_PORT', 22),
            'key' => env('PRODUCTION_SSH_KEY', '~/.ssh/id_rsa'),
        ],
        'web_server' => [
            'type' => 'nginx',
            'config_path' => '/etc/nginx/sites-available/momenikah',
            'site_enabled' => '/etc/nginx/sites-enabled/momenikah',
            'service' => 'nginx',
        ],
        'php_fpm' => [
            'service' => 'php8.3-fpm',
            'pool' => 'momenikah',
        ],
        'database' => [
            'host' => env('PRODUCTION_DB_HOST', 'db.momenikah.internal'),
            'port' => env('PRODUCTION_DB_PORT', 3306),
            'database' => env('PRODUCTION_DB_DATABASE', 'momenikah'),
            'username' => env('PRODUCTION_DB_USERNAME', 'momenikah'),
        ],
        'redis' => [
            'host' => env('PRODUCTION_REDIS_HOST', 'redis.momenikah.internal'),
            'port' => env('PRODUCTION_REDIS_PORT', 6379),
        ],
        'storage' => [
            'disk' => env('PRODUCTION_STORAGE_DISK', 's3'),
            'bucket' => env('PRODUCTION_STORAGE_BUCKET', 'momenikah'),
            'region' => env('PRODUCTION_STORAGE_REGION', 'ap-southeast-1'),
        ],
        'backup' => [
            'enabled' => true,
            'schedule' => '0 2 * * *', // Daily at 02:00 UTC
            'retention_days' => 30,
            'directory' => '/var/backups/momenikah/mysql',
        ],
        'monitoring' => [
            'sentry' => [
                'enabled' => true,
                'environment' => 'production',
            ],
        ],
    ],

    'local' => [
        'name' => 'Local Development',
        'host' => env('APP_URL', 'http://localhost'),
        'ssh' => [
            'user' => env('SSH_USER', 'vagrant'),
            'host' => env('SSH_HOST', '127.0.0.1'),
            'port' => env('SSH_PORT', 2222),
            'key' => env('SSH_KEY', '~/.vagrant/machines/default/virtualbox/private_key'),
        ],
        'web_server' => [
            'type' => 'apache',
            'config_path' => null,
            'site_enabled' => null,
            'service' => 'apache2',
        ],
        'php_fpm' => [
            'service' => 'php8.3-fpm',
            'pool' => null,
        ],
        'database' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3307),
            'database' => env('DB_DATABASE', 'momenikah'),
            'username' => env('DB_USERNAME', 'momenikah'),
        ],
        'redis' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
        ],
        'storage' => [
            'disk' => 'local',
            'bucket' => null,
            'region' => null,
        ],
        'backup' => [
            'enabled' => false,
        ],
        'monitoring' => [
            'sentry' => [
                'enabled' => false,
            ],
        ],
    ],
];
