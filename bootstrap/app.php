<?php

use App\Http\Middleware\AddSurfaceHeader;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Subdomain surfaces are registered BEFORE the root domain surface so that
            // Laravel's router matches app.momenikah.test and undangan.momenikah.test
            // before it evaluates the root momenikah.test domain.
            $surfaces = [
                'app' => [
                    'domain' => config('surfaces.app.domain', 'app.momenikah.test'),
                    'file' => base_path('routes/app.php'),
                ],
                'public' => [
                    'domain' => config('surfaces.public.domain', 'undangan.momenikah.test'),
                    'file' => base_path('routes/public.php'),
                ],
                // Root domain registered last — must come after all subdomain entries.
                'marketing' => [
                    'domain' => config('surfaces.marketing.domain', 'momenikah.test'),
                    'file' => base_path('routes/marketing.php'),
                ],
            ];

            foreach ($surfaces as $key => $config) {
                try {
                    if (! file_exists($config['file'])) {
                        throw new RuntimeException("Route file not found: {$config['file']}");
                    }

                    Route::domain($config['domain'])
                        ->middleware(['web', AddSurfaceHeader::class.':'.$key])
                        ->group($config['file']);
                } catch (Throwable $e) {
                    // Per Requirement 5.11: log the failing route file name and halt
                    // initialization so the application does not start in a broken state.
                    Log::critical("Failed to load route file for surface [{$key}]: {$e->getMessage()}", [
                        'surface' => $key,
                        'file' => $config['file'],
                        'exception' => $e,
                    ]);

                    throw new RuntimeException(
                        "Application initialization halted: could not load route file for surface [{$key}]. "
                        ."File: {$config['file']}. Reason: {$e->getMessage()}",
                        previous: $e
                    );
                }
            }

            // Health check endpoint — domain-agnostic, accessible from any surface.
            // Registered after surface routes so surface fallback does not shadow it.
            Route::get('/health', function () {
                $status = 'healthy';
                $checks = [];
                $httpCode = 200;

                try {
                    DB::connection()->getPdo();
                    $checks['database'] = [
                        'status' => 'connected',
                        'connection' => config('database.default'),
                    ];
                } catch (Throwable $e) {
                    $status = 'unhealthy';
                    $httpCode = 503;
                    $checks['database'] = [
                        'status' => 'disconnected',
                        'error' => $e->getMessage(),
                    ];
                }

                try {
                    Redis::ping();
                    $checks['redis'] = [
                        'status' => 'connected',
                    ];
                } catch (Throwable $e) {
                    $status = 'unhealthy';
                    $httpCode = 503;
                    $checks['redis'] = [
                        'status' => 'disconnected',
                        'error' => $e->getMessage(),
                    ];
                }

                return response()->json([
                    'status' => $status,
                    'timestamp' => now()->toISOString(),
                    'checks' => $checks,
                ], $httpCode);
            })->middleware('web')->name('health.check');

            // Fallback: any request reaching here did not match a configured surface domain.
            // Returns HTTP 404 per Requirement 5.5.
            Route::fallback(function () {
                abort(404, 'Unrecognized surface domain.');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register security headers middleware globally for all web routes
        // This ensures all routes across all surfaces (marketing, app, public) receive security headers
        $middleware->prepend(SecurityHeaders::class);

        // GST-02: the "guest opened" beacon comes from a page cached at the edge, so it cannot carry a CSRF token.
        // The endpoint always answers 204 and is throttled (routes/public.php).
        $middleware->validateCsrfTokens(except: ['*/opened']);

        $middleware->alias([
            'surface' => AddSurfaceHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
