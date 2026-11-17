# Staging Deployment & Rollback Guide

## Staging Environment Setup
- **App Path:** `/var/www/momenikah`
- **Shared Files:** `/var/www/momenikah/shared/.env` dan `/var/www/momenikah/shared/storage`
- **Cron Backup (02:00 UTC):** `0 2 * * * /var/www/momenikah/deploy/backup-db.sh >> /var/log/momenikah-backup.log 2>&1`

## Manual Rollback Procedure
Jika rilis terbaru bermasalah dan perlu dikembalikan ke rilis sebelumnya secara manual:

1. Lihat daftar rilis yang tersedia:
   `ls -lt /var/www/momenikah/releases`
2. Arahkan ulang symlink `current` ke folder rilis sebelumnya:
   `ln -sfn /var/www/momenikah/releases/<TIMESTAMP_SEBELUMNYA> /var/www/momenikah/current`
3. Segarkan cache konfigurasi dan worker PHP-FPM:
   `cd /var/www/momenikah/current && php artisan config:cache && php artisan route:cache && php artisan queue:restart && sudo systemctl reload php8.3-fpm`
4. Pastikan health check kembali normal:
   `curl -i http://127.0.0`
