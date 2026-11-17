#!/usr/bin/env bash
set -euo pipefail

# Database Backup Script for Momenikah
# Usage: ./backup-db.sh [--manifest-only] [--disk=s3]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Configuration
BACKUP_DIR="${PROJECT_DIR}/storage/app/backups"
LOCK_FILE="/tmp/momenikah-db-backup.lock"
DISK="${DISK:-s3}"
MANIFEST_ONLY="${MANIFEST_ONLY:-false}"

# Load environment variables
if [[ -f "${PROJECT_DIR}/.env" ]]; then
    export $(grep -v '^#' "${PROJECT_DIR}/.env" | xargs)
fi

# Concurrency control: wait if backup already in progress
exec 200>"$LOCK_FILE"
flock -w 3600 -x 200

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Generate timestamp
TIMESTAMP=$(date -u +%Y%m%d_%H%M%S)
BACKUP_NAME="backup_${TIMESTAMP}"
BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}"

mkdir -p "$BACKUP_PATH"

# Get database credentials from environment
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-momenikah}"
DB_USERNAME="${DB_USERNAME:-momenikah}"
DB_PASSWORD="${DB_PASSWORD:-secret}"

# Check if mysqldump is available
if ! command -v mysqldump &> /dev/null; then
    echo "[ERROR] mysqldump command not found"
    exit 1
fi

echo "[INFO] Starting database backup at $(date -u +%Y-%m-%d\ %H:%M:%S) UTC"

# Create database dump
DUMP_FILE="${BACKUP_PATH}/backup.sql"
echo "[INFO] Creating database dump..."

# Build mysqldump command
MYSQLDUMP_CMD="mysqldump --host=${DB_HOST} --port=${DB_PORT} --user=${DB_USERNAME}"
if [[ -n "${DB_PASSWORD}" ]]; then
    MYSQLDUMP_CMD="${MYSQLDUMP_CMD} --password=${DB_PASSWORD}"
fi

MYSQLDUMP_CMD="${MYSQLDUMP_CMD} --single-transaction --quick --routines --triggers ${DB_DATABASE}"

# Execute mysqldump
if ! $MYSQLDUMP_CMD > "$DUMP_FILE" 2>&1; then
    echo "[ERROR] mysqldump failed"
    cat "$DUMP_FILE"
    exit 1
fi

echo "[INFO] Database dump created: ${DUMP_FILE}"

# Generate manifest with row counts
MANIFEST_FILE="${BACKUP_PATH}/manifest.json"
echo "[INFO] Generating manifest with row counts..."

# Build JSON manifest
echo "{" > "$MANIFEST_FILE"
echo "  \"timestamp_utc\": \"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"," >> "$MANIFEST_FILE"
echo "  \"backup_name\": \"${BACKUP_NAME}\"," >> "$MANIFEST_FILE"
echo "  \"database\": \"${DB_DATABASE}\"," >> "$MANIFEST_FILE"
echo "  \"tables\": {" >> "$MANIFEST_FILE"

# Get all tables and their row counts
TABLES=$(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" \
    -e "SELECT table_name FROM information_schema.tables WHERE table_schema = '$DB_DATABASE' AND table_type = 'BASE TABLE';" 2>/dev/null || true)

FIRST=true
while IFS= read -r TABLE; do
    if [[ -z "$TABLE" ]]; then
        continue
    fi

    # Get row count for each table
    COUNT=$(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" \
        -e "SELECT COUNT(*) FROM \`$DB_DATABASE\`.\`$TABLE\`;" 2>/dev/null || echo "0")

    if [[ "$FIRST" == "true" ]]; then
        FIRST=false
    else
        echo "," >> "$MANIFEST_FILE"
    fi

    printf "    \"%s\": %s" "$TABLE" "$COUNT" >> "$MANIFEST_FILE"
done <<< "$TABLES"

echo "" >> "$MANIFEST_FILE"
echo "  }" >> "$MANIFEST_FILE"
echo "}" >> "$MANIFEST_FILE"

echo "[INFO] Manifest created: ${MANIFEST_FILE}"

# If DISK is not s3, just copy to local directory
if [[ "$DISK" != "s3" ]]; then
    echo "[INFO] Backup stored locally at: ${BACKUP_PATH}"

    # Apply retention policy (7 days minimum)
    echo "[INFO] Checking retention policy..."
    find "${BACKUP_DIR}" -maxdepth 1 -type d -name "backup_*" -mtime +7 -exec rm -rf {} \; 2>/dev/null || true

    echo "[SUCCESS] Backup completed at ${BACKUP_PATH}"
    exit 0
fi

# Upload to S3 (if DISK=s3)
if [[ "$DISK" == "s3" ]]; then
    echo "[INFO] Uploading backup to S3..."

    # Check if aws cli is available
    if ! command -v aws &> /dev/null; then
        echo "[ERROR] AWS CLI not found. Please install and configure AWS CLI."
        exit 1
    fi

    # Upload backup files to S3
    S3_BUCKET="${AWS_BUCKET:-momenikah-staging}"
    S3_REGION="${AWS_DEFAULT_REGION:-ap-southeast-1}"
    S3_PATH="backups/${BACKUP_NAME}"

    # Upload backup.sql
    aws s3 cp "$DUMP_FILE" "s3://${S3_BUCKET}/${S3_PATH}/backup.sql" \
        --region "$S3_REGION" \
        --content-type "text/plain" \
        --storage-class "STANDARD_IA" || {
        echo "[ERROR] Failed to upload backup.sql to S3"
        exit 1
    }

    # Upload manifest.json
    aws s3 cp "$MANIFEST_FILE" "s3://${S3_BUCKET}/${S3_PATH}/manifest.json" \
        --region "$S3_REGION" \
        --content-type "application/json" \
        --storage-class "STANDARD_IA" || {
        echo "[ERROR] Failed to upload manifest.json to S3"
        exit 1
    }

    echo "[INFO] Backup uploaded to S3: s3://${S3_BUCKET}/${S3_PATH}/"

    # Apply retention policy on S3
    echo "[INFO] Checking S3 retention policy (min 7 days, max 30 days)..."

    # Delete backups older than 30 days
    aws s3 ls "s3://${S3_BUCKET}/backups/" --region "$S3_REGION" --recursive | \
        awk '{print $1" "$2}' | \
        while read -r date time; do
            backup_date="${date}T${time}Z"
            backup_timestamp=$(date -d "$backup_date" +%s 2>/dev/null || echo "0")
            current_timestamp=$(date +%s)
            age_days=$(( (current_timestamp - backup_timestamp) / 86400 ))

            if [[ $age_days -gt 30 ]]; then
                # Extract backup name from path
                backup_path=$(echo "${date}/${time}" | sed 's/\///g')
                echo "Deleting old backup: ${backup_path} (${age_days} days old)"
                aws s3 rm "s3://${S3_BUCKET}/backups/${backup_path}/" \
                    --region "$S3_REGION" --recursive 2>/dev/null || true
            fi
        done

    # Keep at least 7 days of backups (don't delete if less than 7 days)
    echo "[SUCCESS] Backup completed and uploaded to S3"
    exit 0
fi

echo "[SUCCESS] Backup completed successfully"
exit 0
