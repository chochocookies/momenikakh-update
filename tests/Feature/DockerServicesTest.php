<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Docker Services Integration Test
 *
 * Verifies that Laravel can successfully connect to MySQL and Redis
 * services running in Docker containers.
 *
 * Run with: php artisan test --group=integration
 * Requires: Docker containers (MySQL + Redis) must be running.
 *
 * Requirements: 2.6, 2.7, 2.8, 2.9
 */
#[Group('integration')]
class DockerServicesTest extends TestCase
{
    /**
     * Test that Laravel can connect to MySQL database.
     *
     * Requirement 2.6: MySQL_Service SHALL accept connections within 5 seconds
     * Requirement 2.8: php artisan migrate:status SHALL complete with exit code 0
     */
    public function test_mysql_connection(): void
    {
        $startTime = microtime(true);

        // Attempt to connect to database and execute a simple query
        $result = DB::select('SELECT 1 as test');

        $elapsedTime = microtime(true) - $startTime;

        // Assert connection successful
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result[0]->test);

        // Assert connection completed within 5 seconds (Requirement 2.6)
        $this->assertLessThan(
            5.0,
            $elapsedTime,
            'MySQL connection should complete within 5 seconds'
        );
    }

    /**
     * Test that Laravel can connect to Redis cache.
     *
     * Requirement 2.7: Redis_Service SHALL accept connections within 5 seconds
     * Requirement 2.9: php artisan cache:clear SHALL complete with exit code 0
     */
    public function test_redis_connection(): void
    {
        $startTime = microtime(true);

        // Attempt to connect to Redis and perform a ping
        $ping = Redis::ping();

        $elapsedTime = microtime(true) - $startTime;

        // Support both Predis Status object and PhpRedis string/bool responses
        $pingString = (string) $ping;
        $this->assertTrue(
            $pingString === 'PONG' || $ping === true || $ping === 1 || $pingString === '1',
            'Redis ping should return PONG or true'
        );

        // Assert connection completed within 5 seconds (Requirement 2.7)
        $this->assertLessThan(
            5.0,
            $elapsedTime,
            'Redis connection should complete within 5 seconds'
        );
    }

    /**
     * Test that Redis cache operations work correctly.
     */
    public function test_redis_cache_operations(): void
    {
        $key = 'test_key_'.uniqid();
        $value = 'test_value_'.time();

        // Store a value in Redis
        Redis::set($key, $value);

        // Retrieve the value from Redis
        $retrieved = Redis::get($key);

        // Assert the value matches
        $this->assertEquals($value, $retrieved);

        // Clean up
        Redis::del($key);
    }

    /**
     * Test that database queries execute successfully.
     */
    public function test_mysql_query_execution(): void
    {
        // Test that we can query database metadata
        $result = DB::select('SELECT DATABASE() as current_db');

        // Assert we got a result
        $this->assertNotEmpty($result);
        $this->assertNotNull($result[0]->current_db);

        // Verify it matches our configured database name
        $expectedDb = config('database.connections.mysql.database');
        $this->assertEquals($expectedDb, $result[0]->current_db);
    }

    /**
     * Test that MySQL version meets requirements.
     *
     * Requirement 2.3: MySQL service version 8.4.x
     */
    public function test_mysql_version(): void
    {
        $result = DB::select('SELECT VERSION() as version');
        $version = $result[0]->version;

        // Extract major.minor version (e.g., "8.4.3" -> "8.4")
        preg_match('/^(\d+)\.(\d+)/', $version, $matches);

        $this->assertNotEmpty($matches, 'Could not parse MySQL version');

        $major = (int) $matches[1];
        $minor = (int) $matches[2];

        // Assert MySQL version is 8.4.x (Requirement 2.3)
        $this->assertEquals(8, $major, 'MySQL major version should be 8');
        $this->assertEquals(4, $minor, 'MySQL minor version should be 4');
    }

    /**
     * Test that Redis version meets requirements.
     *
     * Requirement 2.4: Redis service version 7.x or higher
     */
    public function test_redis_version(): void
    {
        $info = Redis::info();

        // Support both PhpRedis flat array and Predis nested Server array
        $version = $info['redis_version'] ?? $info['Server']['redis_version'] ?? null;

        $this->assertNotNull($version, 'Redis version key not found in Redis::info()');

        // Extract major version (e.g., "7.2.4" -> 7)
        preg_match('/^(\d+)/', $version, $matches);

        $this->assertNotEmpty($matches, 'Could not parse Redis version');

        $major = (int) $matches[1];

        // Assert Redis version is 7 or higher (Requirement 2.4)
        $this->assertGreaterThanOrEqual(
            7,
            $major,
            'Redis major version should be 7 or higher'
        );
    }
}
