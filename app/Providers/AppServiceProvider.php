<?php

namespace App\Providers;

use App\Jobs\ExecuteDatabaseBackup;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerBackupSchedule();
    }

    /**
     * Register the backup schedule.
     */
    private function registerBackupSchedule(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new ExecuteDatabaseBackup())
                ->dailyAt('02:00')
                ->timezone('UTC')
                ->onOneServer();
        });
    }
}
