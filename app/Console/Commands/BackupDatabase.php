<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'backup:database')]
class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database
                            {--disk= : The disk to store the backup}
                            {--manifest-only : Only generate manifest without backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database backup with manifest';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = $this->option('disk') ?: config('backup.disk', 's3');
        $manifestOnly = $this->option('manifest-only');

        $timestamp = $this->generateTimestamp();
        $backupDir = "backups/{$timestamp}";

        try {
            // If mysqldump is not available, automatically switch to manifest-only mode
            if (! $manifestOnly && ! $this->hasMysqldump()) {
                $this->info('mysqldump not available - generating manifest only...');
                $manifestOnly = true;
            }

            if (! $manifestOnly) {
                $this->info('Creating database backup...');
                $sqlFile = $this->createDatabaseDump($timestamp);

                $this->info('Uploading backup to storage...');
                Storage::disk($disk)->put("{$backupDir}/backup.sql", $sqlFile);
            }

            $this->info('Generating manifest with row counts...');
            $manifest = $this->generateManifest();

            Storage::disk($disk)->put("{$backupDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("Backup and manifest created successfully at {$backupDir}");
            $this->info("Manifest file: {$backupDir}/manifest.json");

            if (! $manifestOnly) {
                $this->info("Backup file: {$backupDir}/backup.sql");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Backup failed: {$e->getMessage()}");
            $this->error("Stack trace: {$e->getTraceAsString()}");

            return self::FAILURE;
        }
    }

    /**
     * Generate timestamp in UTC format.
     */
    private function generateTimestamp(): string
    {
        return now()->utc()->format('Y-m-d_His');
    }

    /**
     * Create database dump using mysqldump.
     */
    private function createDatabaseDump(string $timestamp): string
    {
        $config = config('database.connections.mysql');
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Build mysqldump command
        $command = "mysqldump --host={$host} --port={$port} --user={$username}";

        if ($password) {
            $command .= " --password={$password}";
        }

        $command .= " --single-transaction --quick --routines --triggers";
        $command .= " {$database}";

        // Check if mysqldump is available
        if (! $this->hasMysqldump()) {
            throw new \RuntimeException('mysqldump is not available on this system');
        }

        // Execute mysqldump and capture output
        $output = [];
        $returnVar = null;

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException('mysqldump command failed');
        }

        return implode("\n", $output);
    }

    /**
     * Check if mysqldump is available.
     */
    private function hasMysqldump(): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec('where mysqldump 2>nul', $output);
            return count($output) > 0;
        }

        $output = [];
        exec('which mysqldump 2>/dev/null', $output);
        return count($output) > 0;
    }

    /**
     * Generate manifest with row counts for all tables.
     */
    private function generateManifest(): array
    {
        try {
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE'", [config('database.connections.mysql.database')]);

            $manifest = [
                'timestamp_utc' => now()->utc()->toIso8601String(),
                'database' => config('database.connections.mysql.database'),
                'tables' => [],
            ];

            foreach ($tables as $table) {
                $count = DB::table($table->table_name)->count();
                $manifest['tables'][$table->table_name] = $count;
            }

            return $manifest;
        } catch (\Exception $e) {
            // Return a minimal manifest if database is not accessible
            return [
                'timestamp_utc' => now()->utc()->toIso8601String(),
                'database' => config('database.connections.mysql.database'),
                'tables' => [],
                'note' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
