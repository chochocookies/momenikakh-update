# Manual Rollback Procedures

This document describes how to manually rollback a deployment if automatic rollback fails or if you need to revert to a previous release.

## Overview

The deployment system uses symbolic link swapping to achieve zero-downtime deployments. The `current` symlink points to the active release directory. To rollback manually, you update this symlink to point to a previous release.

## When to Use Manual Rollback

Manual rollback should be used when:

- Automatic rollback fails due to network issues or server problems
- You need to rollback to a specific release that's not the immediate previous one
- You want to inspect the issue before performing automatic rollback
- The automatic rollback mechanism is being tested or debugged

## Prerequisites

- SSH access to the staging server
- Write permissions to `/var/www/momenikah` directory
- Knowledge of the target release timestamp

## Manual Rollback Procedure

### Step 1: SSH to the Server

```bash
ssh deploy@staging.momenikah.test
```

### Step 2: Navigate to the Application Directory

```bash
cd /var/www/momenikah
```

### Step 3: List Available Releases

```bash
ls -la releases/
```

Example output:
```
total 0
drwxr-xr-x 5 deploy deploy 4096 Jan 24 2025 .
drwxr-xr-x 3 deploy deploy 4096 Jan 24 2025 ..
drwxr-xr-x 3 deploy deploy 4096 Jan 24 12:00 20250124_120000
drwxr-xr-x 3 deploy deploy 4096 Jan 24 14:30 20250124_143000
drwxr-xr-x 3 deploy deploy 4096 Jan 24 16:45 20250124_164500
```

### Step 4: Identify the Target Release

Choose the release you want to rollback to. You can:

- Rollback to the previous release (immediate predecessor)
- Rollback to a specific release by timestamp
- Rollback to any release in the `releases/` directory

### Step 5: Update the Current Symlink

#### Option A: Rollback to Previous Release

```bash
# Get the previous release (second most recent)
PREVIOUS_RELEASE=$(ls -td releases/*/ | sed -n 2p)

# Update the symlink
ln -sfn "$PREVIOUS_RELEASE" current

# Verify the symlink
ls -la current
```

#### Option B: Rollback to Specific Release

```bash
# Specify the exact release timestamp
RELEASE_TIMESTAMP="20250124_120000"

# Update the symlink
ln -sfn "/var/www/momenikah/releases/$RELEASE_TIMESTAMP" current

# Verify the symlink
ls -la current
```

### Step 6: Clear Caches

```bash
cd current
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Step 7: Restart Queue Workers

```bash
php artisan queue:restart
```

### Step 8: Reload PHP-FPM

```bash
sudo systemctl reload php-fpm
```

### Step 9: Verify the Rollback

```bash
# Check the symlink
ls -la current

# Verify application is accessible
curl -s https://staging.momenikah.test/health

# Check application version
php artisan --version
```

## Example Commands

### Rollback to Immediate Previous Release

```bash
# SSH to server
ssh deploy@staging.momenikah.test

# Navigate to app directory
cd /var/www/momenikah

# Rollback to previous release
PREVIOUS_RELEASE=$(ls -td releases/*/ | sed -n 2p)
ln -sfn "$PREVIOUS_RELEASE" current

# Clear caches and reload
cd current
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan queue:restart
sudo systemctl reload php-fpm

# Verify
curl -s https://staging.momenikah.test/health
```

### Rollback to Specific Timestamp

```bash
# SSH to server
ssh deploy@staging.momenikah.test

# Navigate to app directory
cd /var/www/momenikah

# Rollback to specific release
RELEASE_TIMESTAMP="20250124_120000"
ln -sfn "/var/www/momenikah/releases/$RELEASE_TIMESTAMP" current

# Clear caches and reload
cd current
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan queue:restart
sudo systemctl reload php-fpm

# Verify
curl -s https://staging.momenikah.test/health
```

### Rollback Using One-Liner Commands

```bash
# One-liner to rollback to previous release
ssh deploy@staging.momenikah.test "cd /var/www/momenikah && PREVIOUS=\$(ls -td releases/*/ | sed -n 2p) && ln -sfn \"\$PREVIOUS\" current && cd \"\$PREVIOUS\" && php artisan config:clear && php artisan route:clear && php artisan queue:restart && sudo systemctl reload php-fpm"

# One-liner to rollback to specific release
ssh deploy@staging.momenikah.test "cd /var/www/momenikah && ln -sfn releases/20250124_120000 current && cd releases/20250124_120000 && php artisan config:clear && php artisan route:clear && php artisan queue:restart && sudo systemctl reload php-fpm"
```

## Monitoring Rollback

### Check Deployment Status

```bash
# Verify current release
ls -la /var/www/momenikah/current

# Check symlink target
readlink -f /var/www/momenikah/current

# Verify files exist
ls -la /var/www/momenikah/current/
```

### Verify Health Endpoint

```bash
# Check health endpoint
curl -s https://staging.momenikah.test/health

# Check with verbose output
curl -v https://staging.momenikah.test/health
```

### Check Application Logs

```bash
# View recent application logs
tail -f /var/www/momenikah/current/storage/logs/laravel.log

# Check for errors in the last 100 lines
tail -100 /var/www/momenikah/current/storage/logs/laravel.log
```

### Check PHP-FPM Status

```bash
# Verify PHP-FPM is running
sudo systemctl status php-fpm

# Check PHP-FPM logs
sudo tail -f /var/log/php8.3-fpm.log
```

## Rollback Verification Checklist

After rollback, verify:

- [ ] Current symlink points to the correct release
- [ ] Health endpoint returns HTTP 200
- [ ] Application is accessible on all surfaces
- [ ] Database connections are working
- [ ] Redis connections are working
- [ ] Queue workers are running
- [ ] No errors in Laravel logs
- [ ] No errors in PHP-FPM logs
- [ ] No errors in Nginx error logs

## Troubleshooting

### Symlink Update Failed

```bash
# Check permissions
ls -la /var/www/momenikah/

# Verify release directory exists
ls -la /var/www/momenikah/releases/

# Manually create symlink
ln -sfn "/var/www/momenikah/releases/TIMESTAMP" "/var/www/momenikah/current"

# Verify symlink
ls -la current
```

### Cache Clear Failed

```bash
# Try clearing caches individually
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Or clear all at once
php artisan optimize:clear
```

### PHP-FPM Reload Failed

```bash
# Check PHP-FPM status
sudo systemctl status php-fpm

# Try restarting
sudo systemctl restart php-fpm

# Check for errors
sudo journalctl -xeu php-fpm
```

### Health Check Still Fails

```bash
# Check if application is accessible
curl -v https://staging.momenikah.test/

# Check database connectivity
php artisan tinker --execute="DB::select('SELECT 1');"

# Check Redis connectivity
php artisan tinker --execute="Cache::store('redis')->flush();"

# Check application logs
tail -100 /var/www/momenikah/current/storage/logs/laravel.log
```

## Automatic vs Manual Rollback

| Aspect | Automatic Rollback | Manual Rollback |
|--------|-------------------|-----------------|
| Trigger | Post-deployment health check failure | User-initiated |
| Time to Rollback | Within 60 seconds | Minutes (user-dependent) |
| Target Release | Previous release | Any release |
| User Intervention | None required | Required |
| Best For | Immediate failures | Investigation, specific releases |

## Contact

If you encounter issues during manual rollback:

1. Check the deployment logs at `/var/www/momenikah/shared/deploy-logs/`
2. Review Laravel logs at `/var/www/momenikah/current/storage/logs/laravel.log`
3. Check PHP-FPM logs at `/var/log/php8.3-fpm.log`
4. Review Nginx logs at `/var/log/nginx/momenikah-staging-error.log`
5. Contact the DevOps team if the issue persists
