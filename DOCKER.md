# Docker Environment Setup

This document describes the Docker Compose configuration for local development of the Momenikah platform.

## Services

The Docker Compose configuration provides two services:

### MySQL 8.4

- **Port**: 3306
- **Container Name**: `momenikah_mysql`
- **Default Database**: `momenikah`
- **Default User**: `momenikah`
- **Default Password**: `secret`
- **Data Volume**: `mysql_data` (persisted at `/var/lib/mysql`)

The MySQL service includes health checks to ensure it's ready before the application connects.

### Redis 7.x

- **Port**: 6379
- **Container Name**: `momenikah_redis`
- **Data Volume**: `redis_data` (persisted at `/data`)
- **Persistence**: AOF (Append Only File) enabled for data durability

The Redis service uses Alpine Linux for a smaller image size and includes health checks.

## Named Volumes

The configuration uses named Docker volumes for data persistence:

- `momenikah_mysql_data`: MySQL database files
- `momenikah_redis_data`: Redis persistence files

These volumes persist data across container restarts and removals.

## Prerequisites

1. **Docker Desktop** must be installed and running
2. **Ports 3306 and 6379** must be available on your system

## Quick Start

### 1. Check Prerequisites

Run the environment check script before starting services:

**Windows:**
```cmd
docker-check.bat
```

**macOS/Linux:**
```bash
chmod +x docker-check.sh
./docker-check.sh
```

This script verifies:
- Docker daemon is running
- Required ports are available
- Docker Compose is installed

### 2. Start Services

```bash
docker-compose up -d
```

The `-d` flag runs services in detached mode (background).

### 3. Verify Services

Check that services are running:

```bash
docker-compose ps
```

Both `momenikah_mysql` and `momenikah_redis` should show status "Up" with healthy health checks.

### 4. Test Laravel Connectivity

Test database connection:
```bash
php artisan migrate:status
```

Test Redis connection:
```bash
php artisan cache:clear
```

Both commands should complete with exit code 0.

## Common Commands

### View Service Logs

```bash
# All services
docker-compose logs -f

# MySQL only
docker-compose logs -f mysql

# Redis only
docker-compose logs -f redis
```

### Stop Services

```bash
docker-compose stop
```

Services stop but containers and volumes are preserved.

### Start Stopped Services

```bash
docker-compose start
```

### Restart Services

```bash
docker-compose restart
```

### Stop and Remove Containers

```bash
docker-compose down
```

⚠️ **Note**: This removes containers but preserves volumes (data is kept).

### Stop and Remove Containers + Volumes

```bash
docker-compose down -v
```

⚠️ **Warning**: This **permanently deletes** all data in MySQL and Redis!

## Environment Variables

The Docker Compose configuration reads database credentials from your `.env` file:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=momenikah
DB_USERNAME=momenikah
DB_PASSWORD=secret

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

If these variables are not set, the following defaults are used:
- Database: `momenikah`
- Username: `momenikah`
- Password: `secret`

## Troubleshooting

### Error: Docker daemon is not running

**Cause**: Docker Desktop is not started.

**Solution**: 
1. Open Docker Desktop
2. Wait for it to fully start (whale icon should be steady, not animated)
3. Run `docker-check.bat` (Windows) or `./docker-check.sh` (macOS/Linux) again

### Error: Port 3306 already in use

**Cause**: You have MySQL running locally on your system.

**Solutions**:
1. Stop your local MySQL service:
   - Windows: Services → MySQL → Stop
   - macOS: `brew services stop mysql`
   - Linux: `sudo systemctl stop mysql`

2. Or modify `docker-compose.yml` to use a different port:
   ```yaml
   ports:
     - "3307:3306"  # Use port 3307 on host instead
   ```
   Then update your `.env` file: `DB_PORT=3307`

### Error: Port 6379 already in use

**Cause**: You have Redis running locally on your system.

**Solutions**:
1. Stop your local Redis service:
   - macOS: `brew services stop redis`
   - Linux: `sudo systemctl stop redis`

2. Or modify `docker-compose.yml` to use a different port:
   ```yaml
   ports:
     - "6380:6379"  # Use port 6380 on host instead
   ```
   Then update your `.env` file: `REDIS_PORT=6380`

### Connection Refused When Running Migrations

**Cause**: MySQL container is not fully started.

**Solution**:
1. Check service health: `docker-compose ps`
2. Wait for health check to show "healthy"
3. View logs: `docker-compose logs mysql`
4. Try again after 30 seconds

### Laravel Can't Connect to Redis

**Cause**: Redis container is not fully started or wrong configuration.

**Solution**:
1. Verify Redis is running: `docker-compose ps`
2. Check your `.env` file has correct Redis settings
3. Clear config cache: `php artisan config:clear`
4. Test connection: `php artisan cache:clear`

## Health Checks

Both services include health checks that verify they're ready to accept connections:

- **MySQL**: Checks using `mysqladmin ping` every 10 seconds
- **Redis**: Checks using `redis-cli ping` every 10 seconds

Health checks start after a 30-second initial period (MySQL only) and retry up to 5 times before marking the service as unhealthy.

## Data Persistence

### Backup MySQL Data

```bash
# Export database
docker-compose exec mysql mysqldump -u momenikah -psecret momenikah > backup.sql

# Import database
docker-compose exec -T mysql mysql -u momenikah -psecret momenikah < backup.sql
```

### Backup Redis Data

Redis automatically persists data to the `redis_data` volume using AOF (Append Only File).

To create a manual snapshot:
```bash
docker-compose exec redis redis-cli BGSAVE
```

## Network

Services communicate via a dedicated bridge network named `momenikah_network`. This isolates the services from other Docker containers and provides automatic DNS resolution between services.

## Production Note

⚠️ This Docker Compose configuration is designed for **local development only**. 

For production deployments:
- Use managed database services (e.g., AWS RDS, Digital Ocean Managed Databases)
- Use managed Redis services (e.g., AWS ElastiCache, Redis Cloud)
- Never use default passwords
- Enable SSL/TLS for all connections
- Configure proper backup and monitoring

## Requirements Met

This Docker Compose configuration satisfies the following requirements from the FND-Fondasi-M0 specification:

- ✅ **Requirement 2.3**: MySQL 8.4.x service on port 3306
- ✅ **Requirement 2.4**: Redis 7.x+ service on port 6379
- ✅ **Requirement 2.5**: Named volumes for MySQL and Redis data directories
- ✅ **Requirement 2.11**: Error handling for Docker daemon not running (via check scripts)
- ✅ **Requirement 2.12**: Error handling for port conflicts (via check scripts)

## Additional Resources

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [MySQL Docker Image](https://hub.docker.com/_/mysql)
- [Redis Docker Image](https://hub.docker.com/_/redis)
