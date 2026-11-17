<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk where database backups will be stored.
    |
    */

    'disk' => env('BACKUP_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | The retention policy defines how long backups are kept.
    | Minimum retention ensures at least this many days of backups are kept.
    | Maximum retention ensures backups are deleted after this many days.
    |
    */

    'retention' => [
        'min_days' => env('BACKUP_MIN_RETENTION_DAYS', 7),
        'max_days' => env('BACKUP_MAX_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Manifest
    |--------------------------------------------------------------------------
    |
    | The manifest contains metadata about the backup including row counts
    | for each table, which can be used for verification.
    |
    */

    'manifest' => [
        'enabled' => true,
        'include_row_counts' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Concurrency Control
    |--------------------------------------------------------------------------
    |
    | When enabled, only one backup process can run at a time.
    | If a backup is already in progress, subsequent backups will wait.
    |
    */

    'concurrency' => [
        'enabled' => true,
        'lock_timeout' => env('BACKUP_LOCK_TIMEOUT', 3600), // 1 hour
    ],
];
