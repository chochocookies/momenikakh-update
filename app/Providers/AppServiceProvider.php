<?php

namespace App\Providers;

use App\Jobs\ExecuteDatabaseBackup;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // DOM-13: month and day names in Indonesian everywhere Carbon prints them (translatedFormat, diffForHumans).
        Carbon::setLocale((string) config('app.locale'));

        // Livewire (the invitation editor) talks to the app surface only: never to the marketing or the public host.
        Livewire::setUpdateRoute(fn ($handle) => Route::post('/livewire/update', $handle)->domain((string) config('surfaces.app.domain'))->middleware('web'));
        $this->registerBackupSchedule();
    }

    /**
     * Register the backup schedule.
     */
    private function registerBackupSchedule(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new ExecuteDatabaseBackup)
                ->dailyAt('02:00')
                ->timezone('UTC')
                ->onOneServer();
        });
    }
}
