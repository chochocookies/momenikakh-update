<?php

namespace App\Console;

use App\Jobs\CleanupOldBackups;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run backup cleanup weekly (every Sunday at 03:00 UTC)
        $schedule->job(new CleanupOldBackups)
            ->weekly()
            ->mondays()
            ->at('03:00')
            ->timezone('UTC')
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
