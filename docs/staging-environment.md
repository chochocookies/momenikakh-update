# Staging Environment Setup

This document describes the staging server environment configuration for Momenikah.

## Overview

The staging environment (`staging.momenikah.test`) is designed to closely mirror production while providing developers with debugging capabilities. It uses the same infrastructure as production but with less restrictive settings for easier troubleshooting.

## Environment Details

### URLs

- **Staging Application**: https://staging.momenikah.test
- **Staging App Surface**: https://staging-app.momenikah.test
- **Staging Public Surface**: https://staging-undangan.momenikah.test

### SSH Access

- **Host**: staging.momenikah.test
- **User**: deploy
- **Port**: 22
- **Key**: ~/.ssh/id_rsa (or as configured in config/servers.php)

### Database

- **Host**: staging-db.momenikah.internal
- **Port**: 3306
- **Database**: momenikah_staging
- **Username**: momenikah_staging
- **Password**: [STORED IN SECRETS MANAGER]

### Redis

- **Host**: staging-redis.momenikah.internal
- **Port**: 6379
- **Password**: [STORED IN SECRETS MANAGER]

### Storage

- **Service**: AWS S3
- **Bucket**: momenikah-staging
- **Region**: ap-southeast-1
- **URL**: https://momenikah-staging.s3.ap-southeast-1.amazonaws.com

## Configuration Files

### Environment File

The staging environment variables are stored in `.env.staging`. This file should be copied to the server as `.env`.

```bash
# Example: Copy .env.staging to server
scp .env.staging deploy@staging.momenikah.test:/var/www/momenikah/shared/.env
```

### Key Configuration Files

| File | Purpose |
|------|---------|
| `.env.staging` | Environment variables for staging |
| `config/staging.php` | Staging-specific application configuration |
| `config/servers.php` | Server configuration for all environments |
| `config/filesystems.php` | Filesystem disk configuration including S3 |

## Setup Instructions

### 1. SSH Access Setup

1. **Generate SSH key** (if not already created):
   ```bash
   ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa
   ```

2. **Add public key to server**:
   ```bash
   ssh-copy-id deploy@staging.momenikah.test
   ```

3. **Test SSH connection**:
   ```bash
   ssh deploy@staging.momenikah.test
   ```

### 2. Server Preparation

On the staging server, run the following commands:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y nginx php8.3-fpm php8.3-mysql php8.3-redis php8.3-s3 php8.3-xml php8.3-mbstring php8.3-curl php8.3-gd php8.3-bcmath

# Install MySQL 8.4
sudo apt install -y mysql-server

# Install Redis 7.x
sudo apt install -y redis-server

# Configure MySQL
sudo mysql_secure_installation

# Configure Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Create deployment directory
sudo mkdir -p /var/www/momenikah
sudo chown deploy:deploy /var/www/momenikah
```

### 3. Configure Environment Variables

1. **Copy `.env.staging` to server**:
   ```bash
   scp .env.staging deploy@staging.momenikah.test:/var/www/momenikah/shared/.env
   ```

2. **Edit `.env` on server** and set:
   - `DB_PASSWORD` - database password
   - `DB_USERNAME` - database username (momenikah_staging)
   - `REDIS_PASSWORD` - Redis password
   - `AWS_ACCESS_KEY_ID` - S3 access key
   - `AWS_SECRET_ACCESS_KEY` - S3 secret key
   - `MAIL_USERNAME` - SMTP username
   - `MAIL_PASSWORD` - SMTP password
   - `SENTRY_LARAVEL_DSN` - Sentry DSN
   - `HORIZON_PASSWORD` - Horizon dashboard password

3. **Set proper permissions**:
   ```bash
   sudo chown www-data:www-data /var/www/momenikah/shared/.env
   sudo chmod 600 /var/www/momenikah/shared/.env
   ```

### 4. Configure Nginx

Create `/etc/nginx/sites-available/momenikah-staging`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name staging.momenikah.test;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name staging.momenikah.test staging-app.momenikah.test staging-undangan.momenikah.test;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/staging.momenikah.test.crt;
    ssl_certificate_key /etc/ssl/private/staging.momenikah.test.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;

    # Document root
    root /var/www/momenikah/current/public;

    # Index files
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/momenikah-staging-access.log;
    error_log /var/log/nginx/momenikah-staging-error.log;

    # Security headers (staging mode)
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header X-Frame-Options "SAMEORIGIN";
    add_header Strict-Transport-Security "max-age=86400; includeSubDomains" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;

    # Proxy to PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Serve static files
    location /storage {
        alias /var/www/momenikah/current/storage/app/public;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

Enable the site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/momenikah-staging /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Configure PHP-FPM

Edit `/etc/php/8.3/fpm/pool.d/momenikah.conf`:

```ini
[momenikah]
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 5
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500
chdir = /var/www/momenikah/current
env[APP_ENV] = staging
env[DB_PASSWORD] = $DB_PASSWORD
env[REDIS_PASSWORD] = $REDIS_PASSWORD
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

### 6. Create Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE momenikah_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'momenikah_staging'@'%' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON momenikah_staging.* TO 'momenikah_staging'@'%';
FLUSH PRIVILEGES;
EXIT;
```

### 7. Setup Storage

```bash
# Create storage directories
sudo mkdir -p /var/www/momenikah/shared/storage/app/public
sudo chown -R www-data:www-data /var/www/momenikah/shared/storage
sudo chmod -R 775 /var/www/momenikah/shared/storage

# Create symbolic link
sudo ln -sf /var/www/momenikah/shared/storage/app/public /var/www/momenikah/current/public/storage
```

### 8. Run Deployment

```bash
# Deploy the application
cd /var/www/momenikah
git clone --depth 1 --branch v1.0.0 git@github.com:your-org/momenikah.git releases/20250124120000
ln -sfn /var/www/momenikah/releases/20250124120000 /var/www/momenikah/current

# Navigate to release directory
cd /var/www/momenikah/current

# Install dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Deployment

### Automated Deployment

Use the deployment script to deploy a specific tag:

```bash
./deploy/deploy.sh v1.0.0
```

The script will:
1. Clone the repository at the specified tag
2. Install dependencies
3. Run migrations
4. Run health checks
5. Swap the `current` symlink
6. Clear caches

### Manual Deployment

```bash
# Deploy new release
TIMESTAMP=$(date +%Y%m%d%H%M%S)
git clone --depth 1 --branch v1.0.0 git@github.com:your-org/momenikah.git releases/$TIMESTAMP

# Link shared files
ln -sfn /var/www/momenikah/shared/.env releases/$TIMESTAMP/.env
rm -rf releases/$TIMESTAMP/storage
ln -sfn /var/www/momenikah/shared/storage releases/$TIMESTAMP/storage

# Install dependencies
cd releases/$TIMESTAMP
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Activate release
ln -sfn releases/$TIMESTAMP /var/www/momenikah/current
php artisan queue:restart
sudo systemctl reload php8.3-fpm
```

### Rollback

If something goes wrong:

```bash
# List releases
ls -la /var/www/momenikah/releases/

# Rollback to previous release
cd /var/www/momenikah
PREVIOUS_RELEASE=$(ls -td releases/*/ | sed -n 2p)
ln -sfn $PREVIOUS_RELEASE current
cd current
php artisan config:cache
php artisan route:cache
php artisan queue:restart
sudo systemctl reload php8.3-fpm
```

## Monitoring

### Health Check

Access the health check endpoint:

```
https://staging.momenikah.test/health
```

Expected response:
```json
{
  "status": "up",
  "database": "connected",
  "redis": "connected",
  "storage": "connected"
}
```

### Logs

- **Nginx access log**: `/var/log/nginx/momenikah-staging-access.log`
- **Nginx error log**: `/var/log/nginx/momenikah-staging-error.log`
- **PHP-FPM log**: `/var/log/php8.3-fpm.log`
- **Laravel log**: `/var/www/momenikah/current/storage/logs/laravel.log`

### Sentry

Errors are reported to Sentry with the environment set to "staging". View errors at:
https://sentry.io/organizations/{your-org}/projects/{project}/

## Security

### Environment Variables

All sensitive environment variables should be stored in a secrets manager:

- Database password
- Redis password
- AWS credentials
- Mail SMTP credentials
- Sentry DSN
- Horizon password

### HTTPS

The staging environment uses HTTPS with a wildcard certificate for `*.momenikah.test`.

### Rate Limiting

Rate limits are double production limits for easier testing:
- Login: 10 attempts per 60 seconds
- Registration: 5 attempts per 600 seconds

### Debugging

Staging enables additional debugging features:
- Laravel Telescope
- Horizon dashboard
- Query logging
- Slow query threshold: 1000ms

## Backup

Backups are automated via cron job at 02:00 UTC daily.

### Manual Backup

```bash
# Run backup script
./deploy/backup-db.sh

# Backup location
/var/backups/momenikah/mysql/
```

### Restore Backup

```bash
# List available backups
ls -la /var/backups/momenikah/mysql/

# Restore specific backup
gunzip -c /var/backups/momenikah/mysql/backup_20250124_020000.sql.gz | \
    mysql -h staging-db.momenikah.internal -P 3306 -u momenikah_staging -p momenikah_staging
```

## Troubleshooting

### Common Issues

1. **Permission denied**
   ```bash
   sudo chown -R www-data:www-data /var/www/momenikah/current/storage
   sudo chmod -R 775 /var/www/momenikah/current/storage
   ```

2. **Database connection failed**
   - Verify database credentials in `.env`
   - Check MySQL is running: `sudo systemctl status mysql`
   - Verify user permissions: `SHOW GRANTS FOR 'momenikah_staging'@'%';`

3. **Redis connection failed**
   - Verify Redis is running: `sudo systemctl status redis-server`
   - Test connection: `redis-cli -h staging-redis.momenikah.internal ping`

4. **Storage not accessible**
   - Verify symbolic link: `ls -la /var/www/momenikah/current/public/storage`
   - Check S3 credentials in `.env`

## References

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [AWS S3 Configuration](https://laravel.com/docs/filesystem#s3-configuration)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)