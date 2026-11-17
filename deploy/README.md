# Deployment Scripts

This directory contains deployment and infrastructure scripts for Momenikah.

## Scripts

| File | Description |
|------|-------------|
| `deploy.sh` | Main deployment script for zero-downtime deployments with symbolic link swap strategy |
| `deploy.bat` | Windows wrapper for deployment script |
| `backup-db.sh` | Automated database backup script |

## Overview

The deployment script implements Laravel Forge-style zero-downtime deployment with the following features:

- **Symbolic Link Swap**: Atomic symlink update for zero-downtime deployment
- **Timestamp-based Releases**: Each release stored in `releases/TIMESTAMP/` directory
- **Shared Directories**: Storage and `.env` shared across releases
- **Phased Deployment**: Preparation → Verification → Activation → Cleanup
- **Automatic Rollback**: Rollback within 60 seconds on failure
- **Release Cleanup**: Keeps last 5 releases, removes older ones
- **Health Check Verification**: Configurable retries and interval for post-deployment health checks

## Environment Setup

### Staging Environment

See [docs/staging-environment.md](../docs/staging-environment.md) for detailed staging environment setup instructions.

### Production Environment

See [docs/production-environment.md](../docs/production-environment.md) for detailed production environment setup instructions (TODO: create this document).

## Deployment Process

### Automated Deployment (Git Tag)

When a Git tag matching pattern `v*.*.*` is pushed to the repository, the GitHub Actions workflow will:

1. **Trigger**: Automatically initiate deployment within 5 minutes of tag push
2. **Clone**: Get the tagged release from repository
3. **Deploy**: Execute deployment script with all required steps
4. **Verify**: Perform health checks with configurable retries
5. **Activate**: Update symlink atomically for zero-downtime
6. **Monitor**: Continue monitoring for health check failures
7. **Rollback**: Automatically rollback within 60 seconds if health checks fail
8. **Cleanup**: Remove old releases (keep last 5)

**Workflow Configuration**:
- **Trigger Pattern**: `v*.*.*` (semantic versioning tags)
- **Timeout**: 10 minutes maximum
- **Health Check Retries**: 3 (configurable via `HEALTH_CHECK_RETRIES`)
- **Health Check Interval**: 5 seconds (configurable via `HEALTH_CHECK_INTERVAL`)
- **Rollback Timeout**: 60 seconds (configurable via `ROLLBACK_TIMEOUT`)

### Manual Deployment

```bash
# Deploy a specific tag
./deploy.sh v1.0.0

# The script requires:
# - TAG as first argument (Git tag matching v*.*.* pattern)
# - REPO_URL environment variable (optional, defaults to git@github.com:your-org/momenikah.git)
# - HEALTH_URL environment variable (optional, defaults to http://127.0.0.1/health)
```

## Phases

### Phase 1: Preparation
- Create release directory structure
- Clone repository at specified tag
- Link shared directories (storage, .env)

### Phase 2: Dependencies & Assets
- Install Composer dependencies with optimizations
- Install Node.js dependencies and build assets

### Phase 3: Database Migrations
- Run database migrations with seeding
- Ensure database schema is up to date

### Phase 4: Verification (Max 120s)
- Database connectivity checks
- Storage permissions verification
- Redis connectivity checks (if configured)

### Phase 5: Pre-Activation Optimization
- Cache configuration, routes, views, and events

### Phase 6: Activation (Max 30s unavailability)
- Atomic symlink update
- Cache clearing
- Queue worker restart
- PHP-FPM reload

### Phase 7: Post-Deployment Health Check
- HTTP health check endpoint verification
- Ensure service is responding correctly

### Phase 8: Cleanup
- Remove old releases (keep last 5)
- Log deployment details

## Configuration

### Environment Variables

The following environment variables control deployment behavior:

| Variable | Default | Description |
|----------|---------|-------------|
| `REPO_URL` | `git@github.com:your-org/momenikah.git` | Git repository URL |
| `TAG` | (required) | Git tag to deploy (must match v*.*.*) |
| `HEALTH_URL` | `http://127.0.0.1/health` | Health check endpoint URL |
| `APP_DIR` | `/var/www/momenikah` | Application directory on server |
| `RELEASES_TO_KEEP` | `5` | Number of releases to retain |

### Server Configuration

Server-specific configuration is stored in `config/servers.php`. This includes:

- SSH connection details
- Web server configuration
- Database connection details
- Redis connection details
- Storage configuration

## GitHub Actions Workflow

The deployment is triggered by GitHub Actions when a Git tag matching `v*.*.*` pattern is pushed.

### Workflow Configuration

**File**: `.github/workflows/deploy-staging.yml`

**Trigger**: 
```yaml
on:
  push:
    tags:
      - 'v*.*.*'
```

**Timeout**: 10 minutes maximum

**Configuration Variables**:
- `APP_DIR`: Application directory (default: `/var/www/momenikah`)
- `HEALTH_URL`: Health check endpoint (default: `https://staging.momenikah.test/health`)
- `HEALTH_CHECK_RETRIES`: Number of retries (default: 3)
- `HEALTH_CHECK_INTERVAL`: Interval between retries (default: 5 seconds)
- `ROLLBACK_TIMEOUT`: Maximum time for rollback (default: 60 seconds)

**Environment Secrets**:
- `STAGING_HOST`: Staging server hostname/IP
- `STAGING_USER`: SSH username
- `STAGING_SSH_KEY`: SSH private key
- `STAGING_PORT`: SSH port (default: 22)

**Slack Notifications**:
- `SLACK_BOT_TOKEN`: Slack bot token
- `SLACK_CHANNEL_ID`: Channel ID for notifications

## Shared Directories

The following directories are shared across all releases:

| Directory | Purpose |
|-----------|---------|
| `shared/.env` | Environment variables (copied to each release) |
| `shared/storage/app/public` | Public user uploads |
| `shared/storage/app/private` | Private user uploads |
| `shared/storage/framework/cache/data` | Cache files |
| `shared/storage/framework/sessions` | Session files |
| `shared/storage/framework/views` | Compiled views |
| `shared/storage/logs` | Application logs |

## Rollback Procedures

### Automatic Rollback

The deployment script automatically rolls back within 60 seconds if:

1. Health checks fail during deployment
2. Database migrations fail
3. Symlink update fails
4. HTTP health check fails after activation

### Manual Rollback

If automatic rollback fails or manual intervention is needed:

See [docs/rollback-procedures.md](../docs/rollback-procedures.md) for detailed manual rollback procedures with examples.

#### Quick Manual Rollback (Previous Release)

```bash
# SSH to server
ssh deploy@staging.momenikah.test

# Navigate to application directory
cd /var/www/momenikah

# List available releases
ls -la releases/

# Find previous release (second most recent)
PREVIOUS_RELEASE=$(ls -td releases/*/ | sed -n 2p)

# Update symlink to previous release
ln -sfn "$PREVIOUS_RELEASE" current

# Navigate to previous release
cd current

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Restart queue workers
php artisan queue:restart

# Reload PHP-FPM
sudo systemctl reload php-fpm

# Verify deployment
curl -s http://127.0.0.1/health
```

#### Emergency Rollback Script

In case of complete failure:

```bash
#!/bin/bash
# Emergency rollback script
cd /var/www/momenikah
PREVIOUS_RELEASE=$(ls -td releases/*/ | sed -n 2p)
ln -sfn "$PREVIOUS_RELEASE" current
cd current
php artisan optimize:clear
php artisan queue:restart
sudo systemctl reload php-fpm
```

## Database Backup

### Automated Backup

Daily backups are scheduled at 02:00 UTC via cron job.

```bash
# Backup schedule (crontab entry)
0 2 * * * /var/www/momenikah/deploy/backup-db.sh
```

### Manual Backup

```bash
# Run backup manually
./backup-db.sh
```

### Backup Retention

- **Staging**: 14 days minimum, 30 days maximum
- **Production**: 7 days minimum, 30 days maximum

### Backup Location

- **Staging**: `/var/backups/momenikah/mysql/`
- **Production**: `/var/backups/momenikah/mysql/`

### Backup Verification

Each backup includes a manifest file with row counts for verification:

```json
{
  "timestamp_utc": "20250124_020000",
  "database": "momenikah_staging",
  "tables": {
    "users": 1234,
    "invitations": 5678,
    "events": 90
  }
}
```

To verify a backup:

```bash
# Check backup manifest
cat /var/backups/momenikah/mysql/backup_20250124_020000.manifest.json

# Verify row counts match
gunzip -c /var/backups/momenikah/mysql/backup_20250124_020000.sql.gz | \
    grep -E "INSERT INTO" | \
    wc -l
```

## Troubleshooting

### Deployment Fails

1. **Check deployment logs**:
   ```bash
   tail -f /var/www/momenikah/shared/deploy-logs/deploy_*.log
   ```

2. **Verify SSH connectivity**:
   ```bash
   ssh -v deploy@staging.momenikah.test
   ```

3. **Check disk space**:
   ```bash
   df -h /var/www/momenikah
   ```

4. **Verify database connectivity**:
   ```bash
   mysql -h staging-db.momenikah.internal -u momenikah_staging -p
   ```

### Service Not Responding After Deployment

1. **Check PHP-FPM status**:
   ```bash
   sudo systemctl status php-fpm
   ```

2. **Check Nginx error logs**:
   ```bash
   tail -f /var/log/nginx/momenikah-error.log
   ```

3. **Check Laravel logs**:
   ```bash
   tail -f /var/www/momenikah/current/storage/logs/laravel.log
   ```

4. **Test health endpoint**:
   ```bash
   curl -v http://127.0.0.1/health
   ```

### Database Connection Issues

1. **Verify MySQL is running**:
   ```bash
   sudo systemctl status mysql
   ```

2. **Check database credentials**:
   ```bash
   source /var/www/momenikah/shared/.env
   echo $DB_HOST:$DB_PORT
   ```

3. **Test connection**:
   ```bash
   mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD
   ```

### Redis Connection Issues

1. **Verify Redis is running**:
   ```bash
   sudo systemctl status redis-server
   ```

2. **Test connection**:
   ```bash
   redis-cli -h $REDIS_HOST -p $REDIS_PORT ping
   ```

### Permission Issues

```bash
# Fix storage permissions
cd /var/www/momenikah/current
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Security

### SSH Access

- Use SSH key authentication (not passwords)
- Restrict SSH access to specific IP addresses
- Use non-root user for deployments
- Regularly rotate SSH keys

### Environment Variables

- Store sensitive variables in a secrets manager
- Never commit `.env` files to version control
- Use `.env.staging.example` as template
- Rotate credentials regularly

### Database

- Use strong passwords
- Limit database user privileges
- Encrypt sensitive data
- Regular security audits

## References

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Laravel Zero-Downtime Deployment](https://laravel.com/docs/deployment#zero-downtime-deployment)
- [Laravel Forge Deployment](https://forge.laravel.com)
- [AWS S3 Configuration](https://laravel.com/docs/filesystem#s3-configuration)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)
