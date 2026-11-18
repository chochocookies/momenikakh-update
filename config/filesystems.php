<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL', 'http://localhost').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // MED-01: disk for uploaded invitation media (S3-compatible + CDN in production, local folder in dev).
        // Local dev without MinIO: leave MEDIA_DRIVER unset (files go to storage/app/public/media; run `php artisan storage:link`).
        'media' => env('MEDIA_DRIVER', 'local') === 's3'
            ? [
                'driver' => 's3',
                'key' => env('MEDIA_ACCESS_KEY'),
                'secret' => env('MEDIA_SECRET_KEY'),
                'region' => env('MEDIA_REGION', 'auto'),            // R2: "auto"; MinIO: any, e.g. us-east-1
                'bucket' => env('MEDIA_BUCKET'),
                'endpoint' => env('MEDIA_ENDPOINT'),                // R2: https://<account>.r2.cloudflarestorage.com ; MinIO: http://localhost:9000
                'use_path_style_endpoint' => (bool) env('MEDIA_PATH_STYLE', false), // true for MinIO
                'url' => env('MEDIA_CDN_URL'),                      // media URLs are built from this (the CDN), not from the endpoint
                'throw' => true,
                'report' => false,
                // Files have unique, unguessable names and are never overwritten, so they can be cached "forever".
                'options' => ['CacheControl' => 'public, max-age=31536000, immutable'],
                // No 'visibility' on purpose: R2 and many S3-compatible stores reject per-object ACLs.
            ]
            : [
                'driver' => 'local',
                'root' => storage_path('app/public/media'),
                'url' => rtrim((string) env('APP_URL'), '/').'/storage/media',
                'visibility' => 'public',
                'throw' => true,
                'report' => false,
            ],

        'staging' => [
            'driver' => 's3',
            'key' => env('STAGING_AWS_ACCESS_KEY_ID'),
            'secret' => env('STAGING_AWS_SECRET_ACCESS_KEY'),
            'region' => env('STAGING_AWS_DEFAULT_REGION', 'ap-southeast-1'),
            'bucket' => env('STAGING_AWS_BUCKET', 'momenikah-staging'),
            'url' => env('STAGING_AWS_URL', 'https://momenikah-staging.s3.ap-southeast-1.amazonaws.com'),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
