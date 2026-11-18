<?php

use App\Jobs\CleanupOldBackups;
use Illuminate\Support\Facades\Schedule;

Schedule::command('invitations:expire')->hourly()->withoutOverlapping();

// MED-04: remove media nobody uses any more. Run `php artisan media:prune-orphans --dry-run` for the first week.
Schedule::command('media:prune-orphans')->dailyAt('03:15')->withoutOverlapping()->onOneServer();

// Weekly backup cleanup. This used to live in App\Console\Kernel, which Laravel 11+ no longer loads.
Schedule::job(new CleanupOldBackups)->weekly()->mondays()->at('03:00')->timezone('UTC')->onOneServer();
