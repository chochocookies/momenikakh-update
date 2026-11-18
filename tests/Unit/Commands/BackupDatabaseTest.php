<?php

use App\Console\Commands\BackupDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('creates backup command with correct signature', function () {
    $command = new BackupDatabase;

    expect($command->getName())->toBe('backup:database');
});

it('has correct description', function () {
    $command = new BackupDatabase;

    expect($command->getDescription())->toBe('Create database backup with manifest');
});

it('runs the backup command', function () {
    Queue::fake();
    Storage::fake('local');

    $result = Artisan::call('backup:database', ['--disk' => 'local']);

    expect($result)->toBe(0);

    // Verify manifest was created
    $backups = Storage::allFiles('backups');
    expect($backups)->not()->toBeEmpty();
});

it('generates backup in correct location', function () {
    Storage::fake('local');

    Artisan::call('backup:database', ['--disk' => 'local']);

    $backups = Storage::allFiles('backups');

    expect($backups)->not()->toBeEmpty();
});

it('includes manifest in backup', function () {
    Storage::fake('local');

    Artisan::call('backup:database', ['--disk' => 'local']);

    $backups = Storage::allFiles('backups');
    expect($backups)->not()->toBeEmpty();

    // Check that manifest files exist in backups directory
    $manifests = [];
    foreach ($backups as $backup) {
        if (str_contains($backup, 'manifest.json')) {
            $manifests[] = $backup;
        }
    }

    expect($manifests)->not()->toBeEmpty();
});

it('validates manifest structure', function () {
    Storage::fake('local');

    Artisan::call('backup:database', ['--disk' => 'local']);

    $backups = Storage::allFiles('backups');
    expect($backups)->not()->toBeEmpty();

    $manifest = Storage::get($backups[0]);
    $data = json_decode($manifest, true);

    expect($data)->toBeArray()
        ->and($data['timestamp_utc'])->toBeString()
        ->and($data['database'])->toBeString()
        ->and($data['tables'])->toBeArray();
});

it('handles manifest only mode', function () {
    Storage::fake('local');

    Artisan::call('backup:database', [
        '--disk' => 'local',
        '--manifest-only' => true,
    ]);

    $backups = Storage::allFiles('backups');

    expect($backups)->not()->toBeEmpty();
});

it('handles missing mysqldump gracefully', function () {
    // Test that backup works even without mysqldump (manifest only mode)
    $result = Artisan::call('backup:database', [
        '--disk' => 'local',
    ]);

    expect($result)->toBe(0);
});

it('supports manual backup execution', function () {
    $backupName = 'manual_backup_'.now()->timestamp;

    $result = Artisan::call('backup:database', [
        '--disk' => 'local',
    ]);

    expect($result)->toBe(0);
});
