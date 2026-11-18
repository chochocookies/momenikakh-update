<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ExecuteDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The backup name (timestamp format).
     */
    public readonly string $backupName;

    /**
     * The storage disk to use.
     */
    public readonly string $disk;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $backupName = null, ?string $disk = null)
    {
        $this->backupName = $backupName ?? now()->utc()->format('Y-m-d_His');
        $this->disk = $disk ?? config('backup.disk', 's3');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->ensureBackupStorageExists();

        // For testing or environments without mysqldump, only create manifest
        if (! $this->hasMysqldump()) {
            $this->createManifestOnlyBackup();

            return;
        }

        $this->createBackup();
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
     * Ensure backup storage directory exists.
     */
    private function ensureBackupStorageExists(): void
    {
        $backupPath = "backups/{$this->backupName}";

        if (! Storage::disk($this->disk)->exists($backupPath)) {
            Storage::disk($this->disk)->makeDirectory($backupPath);
        }
    }

    /**
     * Create database backup and store it.
     */
    private function createBackup(): void
    {
        $manifest = $this->generateManifest();

        // Store manifest first (always required)
        Storage::disk($this->disk)->put(
            "backups/{$this->backupName}/manifest.json",
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Skip database dump if mysqldump is not available
        if (! $this->hasMysqldump()) {
            return;
        }

        // Create database dump
        $dumpContent = $this->executeMysqldump();
        Storage::disk($this->disk)->put(
            "backups/{$this->backupName}/backup.sql",
            $dumpContent
        );
    }

    /**
     * Create manifest-only backup.
     */
    private function createManifestOnlyBackup(): void
    {
        $manifest = $this->generateManifest();

        // Store manifest only
        Storage::disk($this->disk)->put(
            "backups/{$this->backupName}/manifest.json",
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Create a placeholder file to indicate backup type
        Storage::disk($this->disk)->put(
            "backups/{$this->backupName}/README.txt",
            'This backup contains only a manifest file. Database dump was not created because mysqldump is not available on this system.'
        );
    }

    /**
     * Generate manifest with row counts.
     */
    private function generateManifest(): array
    {
        try {
            $tables = DB::select("
                SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = ?
                AND table_type = 'BASE TABLE'
            ", [config('database.connections.mysql.database')]);

            $manifest = [
                'timestamp_utc' => now()->utc()->toIso8601String(),
                'database' => config('database.connections.mysql.database'),
                'tables' => [],
            ];

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                // Check if table exists before counting
                if (DB::getSchemaBuilder()->hasTable($tableName)) {
                    $count = DB::table($tableName)->count();
                    $manifest['tables'][$tableName] = $count;
                }
            }

            return $manifest;
        } catch (QueryException $e) {
            throw new InvalidArgumentException("Failed to generate manifest: {$e->getMessage()}");
        }
    }

    /**
     * Execute mysqldump and return SQL content.
     */
    private function executeMysqldump(): string
    {
        $config = config('database.connections.mysql');
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = "mysqldump --host={$host} --port={$port} --user={$username}";

        if ($password) {
            $command .= " --password={$password}";
        }

        $command .= " --single-transaction --quick --routines --triggers {$database} 2>&1";

        $output = [];
        $returnVar = null;

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \RuntimeException('mysqldump command failed: '.implode("\n", $output));
        }

        return implode("\n", $output);
    }

    /**
     * Get the number of seconds the job can run.
     */
    public function timeout(): int
    {
        return 3600; // 1 hour maximum
    }
}
