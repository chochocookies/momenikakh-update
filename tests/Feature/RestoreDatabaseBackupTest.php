<?php

use Illuminate\Support\Facades\Storage;

it('cancels restore when user declines confirmation prompt', function () {
    Storage::fake('backups');

    Storage::disk('backups')->put('backups/2026-11-17_020000/manifest.json', json_encode([
        'database' => 'momenikah',
        'tables' => [],
    ]));
    Storage::disk('backups')->put('backups/2026-11-17_020000/backup.sql', 'SELECT 1;');

    $this->artisan('backup:restore', [
        'backupName' => '2026-11-17_020000',
        '--disk' => 'backups',
    ])
        ->expectsConfirmation(
            "Are you sure you want to restore backup '2026-11-17_020000'? This will overwrite the current database.",
            'no'
        )
        ->expectsOutput('Restore cancelled.')
        ->assertExitCode(1);
});
