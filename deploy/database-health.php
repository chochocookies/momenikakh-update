<?php

/**
 * Database and Redis connectivity health check script.
 *
 * This script is used during deployment to verify database and Redis connectivity
 * before the new release goes live.
 *
 * Exit codes:
 *   0 - All checks passed
 *   1 - Database or Redis connection failed
 */

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

$projectRoot = dirname(__DIR__, 1);
require $projectRoot.'/vendor/autoload.php';

$app = require_once $projectRoot.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$exitCode = 0;

// Check database connection
try {
    $db = DB::connection();
    $db->select('SELECT 1');
    echo "database:connected\n";
    echo '  - Driver: '.config('database.connections.mysql.driver')."\n";
    echo '  - Host: '.config('database.connections.mysql.host')."\n";
    echo '  - Port: '.config('database.connections.mysql.port')."\n";
    echo '  - Database: '.config('database.connections.mysql.database')."\n";
} catch (Exception $e) {
    echo "database:failed:{$e->getMessage()}\n";
    $exitCode = 1;
}

// Check Redis connection (if available and configured)
$redisDriver = config('cache.stores.redis.driver');

if ($redisDriver === 'redis') {
    try {
        $redis = Redis::connection();
        $redis->ping();
        echo "redis:connected\n";
        echo '  - Host: '.config('cache.stores.redis.connection')."\n";
        echo '  - Port: '.config('cache.stores.redis.port')."\n";
    } catch (Exception $e) {
        echo "redis:failed:{$e->getMessage()}\n";
        if ($exitCode === 0) {
            $exitCode = 1;
        }
    }
} else {
    echo "redis:skipped (cache driver is $redisDriver)\n";
}

// Check storage permissions
if (is_writable(storage_path())) {
    echo "storage:writable\n";
} else {
    echo "storage:not-writable\n";
    if ($exitCode === 0) {
        $exitCode = 1;
    }
}

exit($exitCode);
