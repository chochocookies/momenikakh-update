<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CleanupOldBackups implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly ?int $minRetentionDays = null,
        public readonly ?int $maxRetentionDays = null,
        public readonly ?string $disk = null,
    ) {
        $this->minRetentionDays = $minRetentionDays ?? config('backup.retention.min_days', 7);
        $this->maxRetentionDays = $maxRetentionDays ?? config('backup.retention.max_days', 30);
        $this->disk = $disk ?? config('backup.disk', 's3');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->validateRetentionConfig();
        $this->cleanupOldBackups();
    }

    /**
     * Validate retention configuration.
     */
    private function validateRetentionConfig(): void
    {
        if ($this->minRetentionDays < 0) {
            throw new InvalidArgumentException("Minimum retention days must be >= 0");
        }
        
        if ($this->maxRetentionDays < $this->minRetentionDays) {
            throw new InvalidArgumentException("Maximum retention days ({$this->maxRetentionDays}) must be >= minimum retention days ({$this->minRetentionDays})");
        }
    }

    /**
     * Cleanup backups older than max retention days.
     */
    private function cleanupOldBackups(): void
    {
        $backupPath = 'backups';
        
        if (! Storage::disk($this->disk)->exists($backupPath)) {
            return;
        }

        $directories = Storage::disk($this->disk)->directories($backupPath);
        
        foreach ($directories as $directory) {
            $timestamp = basename($directory);
            
            if (! $this->isValidBackupTimestamp($timestamp)) {
                continue;
            }

            $backupDate = \DateTime::createFromFormat('Y-m-d_His', $timestamp);
            
            if (! $backupDate) {
                continue;
            }

            $ageInDays = $backupDate->diff(now())->days;

            // Delete backups older than max retention days
            if ($ageInDays > $this->maxRetentionDays) {
                $this->deleteBackupDirectory($directory);
            }
        }
    }

    /**
     * Check if timestamp matches backup format.
     */
    private function isValidBackupTimestamp(string $timestamp): bool
    {
        return (bool) \DateTime::createFromFormat('Y-m-d_His', $timestamp);
    }

    /**
     * Delete backup directory and all its contents.
     */
    private function deleteBackupDirectory(string $directory): void
    {
        try {
            Storage::disk($this->disk)->deleteDirectory($directory);
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Get the number of seconds the job can run.
     */
    public function timeout(): int
    {
        return 600; // 10 minutes maximum
    }
}
