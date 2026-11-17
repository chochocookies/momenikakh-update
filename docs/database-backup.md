# Database Backup System

This document describes the database backup system for Momenikah.

## Overview

The backup system provides:

- **Daily backups** at 02:00 UTC
- **Retention policy**: 7-30 days (configurable)
- **Concurrency control**: Single backup process at a time
- **Verification**: Row count manifest for restore verification
- **Storage**: Local or S3 (configurable)

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Backup Schedule                      │
│                    (02:00 UTC Daily)                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Backup Job                            │
│                (ExecuteDatabaseBackup)                      │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│   Manifest     │  │   SQL Dump     │  │  S3 Upload*    │
│   Generation   │  │   Creation     │  │  (if S3 disk)  │
└────────────────┘  └────────────────┘  └────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                       Storage                               │
│                  (Local or S3 Bucket)                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Retention Policy                       │
│                  (CleanupOldBackups Job)                    │
└─────────────────────────────────────────────────────────────┘
```

## Configuration

### Environment Variables

Add to `.env`:

```env
# Backup Configuration
BACKUP_DISK=local                    # Storage disk (local, s3)
BACKUP_MIN_RETENTION_DAYS=7         # Minimum retention (days)
BACKUP_MAX_RETENTION_DAYS=30        # Maximum retention (days)
BACKUP_LOCK_TIMEOUT=3600            # Lock timeout (seconds)
```

### Configuration File

`config/backup.php`:

```php
return [
    'disk' => env('BACKUP_DISK', 's3'),
    'retention' => [
        'min_days' => env('BACKUP_MIN_RETENTION_DAYS', 7),
        'max_days' => env('BACKUP_MAX_RETENTION_DAYS', 30),
    ],
    'manifest' => [
        'enabled' => true,
        'include_row_counts' => true,
    ],
    'concurrency' => [
        'enabled' => true,
        'lock_timeout' => env('BACKUP_LOCK_TIMEOUT', 3600),
    ],
];
```

## Usage

### Manual Backup

```bash
# Run backup manually
php artisan backup:database

# Specify storage disk
php artisan backup:database --disk=s3

# Generate manifest only (no backup)
php artisan backup:database --manifest-only
```

### Automatic Backup

Backups are scheduled daily at 02:00 UTC via Laravel's scheduler.

### Manual Restore

```bash
# List available backups
ls -la storage/app/backups/

# Restore from backup (interactive)
php artisan backup:restore

# Restore from specific backup
php artisan backup:restore backup_20250124_020000

# Force restore without confirmation
php artisan backup:restore backup_20250124_020000 --force
```

### Cleanup Old Backups

```bash
# Run cleanup manually (deletes backups older than max retention)
php artisan backup:cleanup

# With custom retention
php artisan backup:cleanup --min-retention-days=7 --max-retention-days=30
```

## Backup Storage

### Local Storage

Backups are stored in `storage/app/backups/`:

```
storage/app/backups/
├── backup_20250124_020000/
│   ├── backup.sql       # Database dump
│   └── manifest.json    # Row counts for verification
└── backup_20250125_020000/
    ├── backup.sql
    └── manifest.json
```

### S3 Storage

For production, backups are stored in AWS S3:

```
s3://momenikah-staging/backups/
├── backup_20250124_020000/
│   ├── backup.sql
│   └── manifest.json
└── backup_20250125_020000/
    ├── backup.sql
    └── manifest.json
```

Configure S3 in `.env`:

```env
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=momenikah-staging
```

## Verification

### Manifest Verification

Each backup includes a `manifest.json` file with row counts:

```json
{
  "timestamp_utc": "2025-01-24T02:00:00Z",
  "backup_name": "backup_20250124_020000",
  "database": "momenikah",
  "tables": {
    "users": 1234,
    "invitations": 5678,
    "events": 90
  }
}
```

### Restore Verification

The `backup:restore` command automatically verifies:

1. Manifest file exists
2. SQL dump file exists
3. Row counts match after restoration
4. All tables restored correctly

## Concurrency Control

The backup system uses file locking to ensure only one backup runs at a time:

```bash
# Lock file location
/tmp/momenikah-db-backup.lock

# Lock timeout (default: 1 hour)
BACKUP_LOCK_TIMEOUT=3600
```

If a backup is already in progress, subsequent attempts will wait for completion.

## Monitoring

### Backup Logs

Check backup logs in `storage/logs/`:

```bash
# View recent backup logs
tail -f storage/logs/laravel.log | grep -i backup
```

### Backup Health

Monitor backup health with:

```bash
# Check last backup
ls -lt storage/app/backups/ | head -n 2

# Verify manifest
cat storage/app/backups/backup_*/manifest.json | jq .
```

## Troubleshooting

### Backup Fails

1. **Check database connectivity**:
   ```bash
   mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p
   ```

2. **Check disk space**:
   ```bash
   df -h storage/app/backups/
   ```

3. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Restore Fails

1. **Verify backup exists**:
   ```bash
   ls -la storage/app/backups/backup_*/manifest.json
   ```

2. **Check manifest format**:
   ```bash
   cat storage/app/backups/backup_*/manifest.json | jq .
   ```

3. **Manual restore**:
   ```bash
   gunzip -c storage/app/backups/backup_*/backup.sql.gz | mysql -u user -p database
   ```

### S3 Upload Fails

1. **Check AWS credentials**:
   ```bash
   aws configure list
   ```

2. **Test S3 connectivity**:
   ```bash
   aws s3 ls s3://momenikah-staging/
   ```

3. **Check bucket permissions**:
   ```bash
   aws s3 ls s3://momenikah-staging/backups/
   ```

## Best Practices

1. **Regular Testing**: Test restore operations regularly
2. **Multi-Location Backup**: Consider cross-region backup copies
3. **Access Control**: Restrict backup access to authorized personnel
4. **Encryption**: Encrypt backups at rest for sensitive data
5. **Monitoring**: Set up alerts for backup failures

## References

- [Laravel Backup Documentation](https://laravel.com/docs/backup)
- [AWS S3 Documentation](https://docs.aws.amazon.com/s3/)
- [mysqldump Documentation](https://dev.mysql.com/doc/refman/8.0/en/mysqldump.html)
