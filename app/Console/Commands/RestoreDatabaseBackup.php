<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'backup:restore')]
class RestoreDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore
                            {backupName? : The backup name to restore}
                            {--disk= : The disk where the backup is stored}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = $this->option('disk') ?: config('backup.disk', 's3');
        $backupName = $this->argument('backupName') ?: $this->selectBackup($disk);

        if (! $backupName) {
            $this->error('No backup selected');

            return self::FAILURE;
        }

        // Confirm restore
        if (! $this->option('force')) {
            if (! $this->confirm("Are you sure you want to restore backup '{$backupName}'? This will overwrite the current database.")) {
                $this->info('Restore cancelled.');

                return self::FAILURE;
            }
        }

        try {
            $this->info("Starting restore from backup: {$backupName}");

            // Verify manifest exists
            if (! Storage::disk($disk)->exists("backups/{$backupName}/manifest.json")) {
                $this->error("Manifest file not found for backup: {$backupName}");

                return self::FAILURE;
            }

            // Verify backup.sql exists
            if (! Storage::disk($disk)->exists("backups/{$backupName}/backup.sql")) {
                $this->error("Backup SQL file not found for backup: {$backupName}");

                return self::FAILURE;
            }

            // Read manifest for verification
            $manifest = json_decode((string) Storage::disk($disk)->get("backups/{$backupName}/manifest.json"), true);
            $this->info("Manifest loaded for database: {$manifest['database']}");

            // Restore database
            $this->info('Restoring database...');
            $this->restoreDatabase($disk, $backupName);

            // Verify restore
            $this->info('Verifying restore...');
            if ($this->verifyRestore($manifest)) {
                $this->info('Restore completed successfully!');
                $this->info("Backup manifest: {$backupName}/manifest.json");
                $this->info("Backup SQL: {$backupName}/backup.sql");

                return self::SUCCESS;
            }

            $this->error('Restore verification failed - row counts do not match');

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Restore failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Select backup from list of available backups.
     */
    private function selectBackup(string $disk): ?string
    {
        $backups = Storage::disk($disk)->directories('backups');

        if (empty($backups)) {
            $this->error('No backups found');

            return null;
        }

        // Sort by name (timestamp) descending
        rsort($backups);

        $backupNames = array_map('basename', $backups);

        return $this->choice('Select backup to restore', $backupNames);
    }

    /**
     * Restore database from SQL file.
     */
    private function restoreDatabase(string $disk, string $backupName): void
    {
        $sqlContent = (string) Storage::disk($disk)->get("backups/{$backupName}/backup.sql");

        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

        // Execute each statement
        foreach ($statements as $statement) {
            if (trim($statement) !== '' && $statement !== '--') {
                try {
                    DB::statement($statement);
                } catch (\Exception $e) {
                    // Skip if statement already exists (for idempotent operations)
                    if (! str_contains($e->getMessage(), 'already exists')) {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Verify restore by comparing row counts with manifest.
     */
    private function verifyRestore(array $manifest): bool
    {
        $allMatch = true;

        foreach ($manifest['tables'] as $table => $expectedCount) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                $this->error("Table '{$table}' does not exist after restore");
                $allMatch = false;

                continue;
            }

            $actualCount = DB::table($table)->count();

            if ($actualCount !== $expectedCount) {
                $this->error("Table '{$table}': expected {$expectedCount} rows, got {$actualCount} rows");
                $allMatch = false;
            }
        }

        return $allMatch;
    }
}
