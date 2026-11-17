# Database Backup Manifest Format

The backup manifest is a JSON file that contains metadata about a database backup, including row counts for each table for verification purposes.

## Manifest Structure

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

## Fields

| Field | Type | Description |
|-------|------|-------------|
| `timestamp_utc` | string (ISO 8601) | Timestamp when backup was created in UTC |
| `backup_name` | string | Unique identifier for the backup |
| `database` | string | Name of the database that was backed up |
| `tables` | object | Key-value pairs of table names and row counts |

## Verification

The manifest is used to verify that a restore operation completed successfully by comparing row counts before and after restoration.

### Manual Verification

```bash
# Read manifest
cat storage/app/backups/backup_20250124_020000/manifest.json

# Verify specific table count
TABLE_COUNT=$(mysql -u user -p -e "SELECT COUNT(*) FROM users;" -N)
MANIFEST_COUNT=$(cat storage/app/backups/backup_20250124_020000/manifest.json | jq '.tables.users')

if [ "$TABLE_COUNT" -eq "$MANIFEST_COUNT" ]; then
    echo "Row count matches!"
else
    echo "Row count mismatch: expected $MANIFEST_COUNT, got $TABLE_COUNT"
fi
```

### Automated Verification

The `backup:restore` command automatically verifies row counts after restoration and reports any discrepancies.

## Storage Locations

### Local Backup

```
storage/app/backups/
├── backup_20250124_020000/
│   ├── backup.sql
│   └── manifest.json
└── backup_20250125_020000/
    ├── backup.sql
    └── manifest.json
```

### S3 Backup

```
s3://momenikah-staging/backups/
├── backup_20250124_020000/
│   ├── backup.sql
│   └── manifest.json
└── backup_20250125_020000/
    ├── backup.sql
    └── manifest.json
```

## Retention Policy

- **Minimum retention**: 7 days
- **Maximum retention**: 30 days
- **Cleanup schedule**: Weekly (every Sunday at 03:00 UTC)

Old backups are automatically deleted based on this policy to ensure:
- At least 7 days of backups are always available
- No more than 30 days of backups are stored
- Storage costs are managed effectively

## Best Practices

1. **Regular Verification**: Periodically verify backup integrity by testing restore operations
2. **Manifest Storage**: Always keep the manifest file alongside the backup SQL file
3. **Cross-Region Backup**: For production, consider cross-region backup copies
4. **Access Control**: Restrict access to backup files to authorized personnel only
5. **Encryption**: Encrypt backups at rest for sensitive data
