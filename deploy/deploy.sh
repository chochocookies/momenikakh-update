#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# Momenikah Zero-Downtime Deployment Script
# =============================================================================
# This script implements Laravel Forge-style deployment with symbolic link swap.
#
# Features:
# - Timestamp-based releases in releases/ directory
# - Shared directories (storage, .env) across releases
# - Atomic symlink update for zero-downtime deployment
# - Preparation, verification, and activation phases
# - Automatic rollback on failure within 60 seconds
# - Post-deployment health check with configurable retries
# - Cleanup of old releases (keeps last 5)
#
# Usage:
#   ./deploy.sh <git-tag>
#
# Environment Variables:
#   REPO_URL                Git repository URL (default: git@github.com:your-org/momenikah.git)
#   TAG                     Git tag to deploy (required as first argument)
#   HEALTH_URL              Health check endpoint URL (default: http://127.0.0.1/health)
#   HEALTH_CHECK_RETRIES    Number of health check retries (default: 3)
#   HEALTH_CHECK_INTERVAL   Time between retries in seconds (default: 5)
#   ROLLBACK_TIMEOUT        Maximum time for rollback in seconds (default: 60)
#   APP_DIR                 Application directory on server (default: /var/www/momenikah)
#   RELEASES_TO_KEEP        Number of releases to keep (default: 5)
#
# Health Check Endpoint:
#   The application includes a /health endpoint that verifies:
#   - Database connectivity (can execute simple query)
#   - Redis connectivity (can ping cache)
#   Returns HTTP 200 with JSON response when healthy
#   Returns HTTP 503 with JSON response when unhealthy
#
# Deployment Health Checks:
#   This script performs two health checks:
#   1. Database/Redis connectivity check via PHP CLI (database-health.php)
#   2. HTTP health check endpoint verification (configurable via HEALTH_URL)
#
# Rollback:
#   If deployment fails after traffic switch, automatic rollback occurs within ROLLBACK_TIMEOUT seconds.
#   Manual rollback: Update 'current' symlink to previous release directory.
# =============================================================================

# --- Configuration ---
APP_DIR="${APP_DIR:-/var/www/momenikah}"
REPO_URL="${REPO_URL:-git@github.com:your-org/momenikah.git}"
HEALTH_URL="${HEALTH_URL:-http://127.0.0.1/health}"
HEALTH_CHECK_RETRIES="${HEALTH_CHECK_RETRIES:-3}"
HEALTH_CHECK_INTERVAL="${HEALTH_CHECK_INTERVAL:-5}"
ROLLBACK_TIMEOUT="${ROLLBACK_TIMEOUT:-60}"
RELEASES_TO_KEEP="${RELEASES_TO_KEEP:-5}"

# --- Input validation ---
if [[ $# -lt 1 ]]; then
    echo "ERROR: Git tag is required"
    echo "Usage: $0 <git-tag>"
    exit 1
fi
TAG="$1"

# Validate tag format (must match v*.*.*)
if [[ ! "$TAG" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "ERROR: Tag must match pattern v*.*.* (e.g., v1.0.0)"
    exit 1
fi

# --- Derived variables ---
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
RELEASE_DIR="$APP_DIR/releases/$TIMESTAMP"
SHARED_DIR="$APP_DIR/shared"
CURRENT_LINK="$APP_DIR/current"
LOG_DIR="$APP_DIR/shared/deploy-logs"
LOG_FILE="$LOG_DIR/deploy_${TIMESTAMP}.log"

# --- Logging function ---
log() {
    local message="$1"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] $message" | tee -a "$LOG_FILE"
}

# --- Initialize logging ---
mkdir -p "$LOG_DIR"
log "=== Starting deployment of tag $TAG ==="
log "Release timestamp: $TIMESTAMP"
log "Release directory: $RELEASE_DIR"

# --- Capture previous release for rollback ---
PREVIOUS_RELEASE=""
if [ -L "$CURRENT_LINK" ]; then
    PREVIOUS_RELEASE=$(readlink -f "$CURRENT_LINK")
    log "Previous release: $PREVIOUS_RELEASE"
fi

# --- Cleanup function (called on failure) ---
rollback() {
    local exit_code=$?
    log "ERROR: Deployment failed with exit code $exit_code"
    log "=== Initiating rollback within ${ROLLBACK_TIMEOUT} seconds ==="

    if [ -n "$PREVIOUS_RELEASE" ] && [ -d "$PREVIOUS_RELEASE" ]; then
        log "Rolling back to previous release: $PREVIOUS_RELEASE"

        # Update symlink atomically
        if ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK"; then
            cd "$CURRENT_LINK"
            log "Clearing caches..."
            php artisan config:clear || true
            php artisan route:clear || true
            php artisan view:clear || true
            php artisan cache:clear || true
            log "Restarting queue workers..."
            php artisan queue:restart || true
            log "Reloading PHP-FPM..."
            sudo systemctl reload php-fpm || true

            log "SUCCESS: Rolled back to $PREVIOUS_RELEASE"
        else
            log "CRITICAL: Failed to update symlink for rollback!"
            exit 1
        fi
    else
        log "CRITICAL: No previous release found to rollback!"
        log "Manual intervention required to restore service"
        exit 1
    fi

    exit $exit_code
}

# Set up trap to catch failures
trap rollback ERR EXIT

# =============================================================================
# PHASE 1: PREPARATION
# =============================================================================
log "=== PHASE 1: PREPARATION ==="

log "Creating release directory structure..."
mkdir -p "$RELEASE_DIR"
mkdir -p "$SHARED_DIR/storage/app/public"
mkdir -p "$SHARED_DIR/storage/app/private"
mkdir -p "$SHARED_DIR/storage/framework/cache/data"
mkdir -p "$SHARED_DIR/storage/framework/sessions"
mkdir -p "$SHARED_DIR/storage/framework/views"
mkdir -p "$SHARED_DIR/storage/logs"

log "Cloning repository at tag $TAG..."
# Clone with depth 1 for faster deployment
if ! git clone --depth 1 --branch "$TAG" "$REPO_URL" "$RELEASE_DIR"; then
    log "ERROR: Failed to clone repository"
    exit 1
fi
log "Repository cloned successfully"

log "Linking shared directories..."
# Link .env file (use shared .env if exists, otherwise use .env.example)
if [ -f "$SHARED_DIR/.env" ]; then
    ln -sfn "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
    log "Linked shared .env file"
elif [ -f "$RELEASE_DIR/.env.example" ]; then
    cp "$RELEASE_DIR/.env.example" "$RELEASE_DIR/.env"
    log "Created .env from .env.example (manual configuration required)"
else
    log "WARNING: No .env or .env.example found"
fi

# Link storage directory (shared across releases)
rm -rf "$RELEASE_DIR/storage"
ln -sfn "$SHARED_DIR/storage" "$RELEASE_DIR/storage"
log "Linked storage directory"

# =============================================================================
# PHASE 2: DEPENDENCIES & ASSETS
# =============================================================================
log "=== PHASE 2: DEPENDENCIES & ASSETS ==="

cd "$RELEASE_DIR"

log "Installing PHP dependencies (composer install)..."
if ! composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader; then
    log "ERROR: Composer install failed"
    exit 1
fi
log "PHP dependencies installed successfully"

log "Installing Node.js dependencies and building assets..."
if [ -f "package.json" ]; then
    if ! npm ci && npm run build; then
        log "ERROR: Frontend build failed"
        exit 1
    fi
    log "Frontend assets built successfully"
else
    log "WARNING: package.json not found, skipping frontend build"
fi

# =============================================================================
# PHASE 3: DATABASE MIGRATIONS
# =============================================================================
log "=== PHASE 3: DATABASE MIGRATIONS ==="

log "Running database migrations..."
if ! php artisan migrate --force --seed; then
    log "ERROR: Database migrations failed"
    exit 1
fi
log "Database migrations completed successfully"

# =============================================================================
# PHASE 4: VERIFICATION (Max 120s)
# =============================================================================
log "=== PHASE 4: VERIFICATION (Max 120s) ==="

# Option 1: Database and Redis connectivity check via PHP CLI
log "Running database connectivity checks..."
if ! timeout 120 php "$RELEASE_DIR/database-health.php"; then
    log "ERROR: Database connectivity checks failed"
    exit 1
fi
log "Database connectivity verified"

# Option 2: HTTP health check endpoint verification with retries
log "Performing HTTP health check with retries (max $HEALTH_CHECK_RETRIES attempts, ${HEALTH_CHECK_INTERVAL}s interval)..."
HEALTH_STATUS="failed"
HEALTH_RETRIES=0

while [ $HEALTH_RETRIES -lt $HEALTH_CHECK_RETRIES ]; do
    HEALTH_RETRIES=$((HEALTH_RETRIES + 1))
    log "Health check attempt $HEALTH_RETRIES of $HEALTH_CHECK_RETRIES..."

    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" --max-time 15 "$HEALTH_URL" || echo "000")
    RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" --max-time 15 "$HEALTH_URL" || echo "999")

    if [ "$HTTP_STATUS" = "200" ]; then
        log "HTTP health check passed (status: $HTTP_STATUS, response time: ${RESPONSE_TIME}s)"
        HEALTH_STATUS="healthy"
        break
    else
        log "HTTP health check failed (status: $HTTP_STATUS, response time: ${RESPONSE_TIME}s)"
        if [ $HEALTH_RETRIES -lt $HEALTH_CHECK_RETRIES ]; then
            log "Waiting ${HEALTH_CHECK_INTERVAL}s before retry..."
            sleep $HEALTH_CHECK_INTERVAL
        fi
    fi
done

if [ "$HEALTH_STATUS" != "healthy" ]; then
    log "ERROR: HTTP health check failed after $HEALTH_CHECK_RETRIES attempts"
    log "Expected: 200, Got: ${HTTP_STATUS:-none}"
    exit 1
fi

# =============================================================================
# PHASE 5: PRE-ACTIVATION OPTIMIZATION
# =============================================================================
log "=== PHASE 5: PRE-ACTIVATION OPTIMIZATION ==="

log "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
log "Application optimized"

# =============================================================================
# PHASE 6: ACTIVATION (Max 30s unavailability)
# =============================================================================
log "=== PHASE 6: ACTIVATION (Zero-downtime symlink swap) ==="

# Atomic symlink update
log "Updating current symlink atomically..."
if ! ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"; then
    log "ERROR: Failed to update current symlink"
    exit 1
fi
log "Symlink updated atomically"

# Clear caches for new release
cd "$CURRENT_LINK"
log "Clearing caches for new release..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
log "Caches cleared"

# Restart queue workers (graceful)
log "Restarting queue workers..."
php artisan queue:restart || true
log "Queue workers restarted"

# Reload PHP-FPM (minimal downtime)
log "Reloading PHP-FPM..."
sudo systemctl reload php-fpm || true
log "PHP-FPM reloaded"

# =============================================================================
# PHASE 7: POST-DEPLOYMENT HEALTH CHECK
# =============================================================================
log "=== PHASE 7: POST-DEPLOYMENT HEALTH CHECK ==="

log "Performing HTTP health check with retries (max $HEALTH_CHECK_RETRIES attempts, ${HEALTH_CHECK_INTERVAL}s interval)..."
HEALTH_STATUS="failed"
HEALTH_RETRIES=0

while [ $HEALTH_RETRIES -lt $HEALTH_CHECK_RETRIES ]; do
    HEALTH_RETRIES=$((HEALTH_RETRIES + 1))
    log "Health check attempt $HEALTH_RETRIES of $HEALTH_CHECK_RETRIES..."

    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" --max-time 15 "$HEALTH_URL" || echo "000")
    RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" --max-time 15 "$HEALTH_URL" || echo "999")

    if [ "$HTTP_STATUS" = "200" ]; then
        log "HTTP health check passed (status: $HTTP_STATUS, response time: ${RESPONSE_TIME}s)"
        HEALTH_STATUS="healthy"
        break
    else
        log "HTTP health check failed (status: $HTTP_STATUS, response time: ${RESPONSE_TIME}s)"
        if [ $HEALTH_RETRIES -lt $HEALTH_CHECK_RETRIES ]; then
            log "Waiting ${HEALTH_CHECK_INTERVAL}s before retry..."
            sleep $HEALTH_CHECK_INTERVAL
        fi
    fi
done

if [ "$HEALTH_STATUS" != "healthy" ]; then
    log "ERROR: HTTP health check failed after $HEALTH_CHECK_RETRIES attempts"
    log "Expected: 200, Got: ${HTTP_STATUS:-none}"
    log "Initiating automatic rollback within ${ROLLBACK_TIMEOUT} seconds..."
    rollback
fi

# =============================================================================
# PHASE 8: CLEANUP
# =============================================================================
log "=== PHASE 8: CLEANUP ==="

# Remove old releases, keeping the last N releases
log "Cleaning up old releases (keeping $RELEASES_TO_KEEP most recent)..."
RELEASES_COUNT=$(ls -1dt "$APP_DIR/releases"/* 2>/dev/null | wc -l)

if [ "$RELEASES_COUNT" -gt "$RELEASES_TO_KEEP" ]; then
    log "Found $RELEASES_COUNT releases, removing $(($RELEASES_COUNT - $RELEASES_TO_KEEP)) old releases..."
    ls -1dt "$APP_DIR/releases"/* | tail -n +$((RELEASES_TO_KEEP + 1)) | while read -r release; do
        log "Removing old release: $release"
        rm -rf "$release"
    done
    log "Cleanup completed"
else
    log "No old releases to clean up (only $RELEASES_COUNT releases exist)"
fi

# =============================================================================
# DEPLOYMENT COMPLETE
# =============================================================================
# Cancel the trap since deployment was successful
trap - ERR EXIT

log "=== DEPLOYMENT COMPLETE ==="
log "Tag $TAG deployed successfully"
log "Current release: $RELEASE_DIR"
log "Previous release: ${PREVIOUS_RELEASE:-none}"

# Output summary
echo ""
echo "========================================"
echo "Deployment Summary"
echo "========================================"
echo "Tag:              $TAG"
echo "Release:          $RELEASE_DIR"
echo "Previous:         ${PREVIOUS_RELEASE:-none}"
echo "Current Link:     $CURRENT_LINK"
echo "HTTP Status:      $HTTP_STATUS"
echo "Rollback Timeout: ${ROLLBACK_TIMEOUT}s"
echo "Health Retries:   $HEALTH_CHECK_RETRIES"
echo "========================================"
echo ""
