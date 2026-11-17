<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Register Livewire-powered auth views
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        // Login throttling: 5 percobaan per email per 60 detik (percobaan ke-6 kena lockout 60 detik)
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey)->response(function (Request $request, array $headers) {
                $seconds = $headers['Retry-After'] ?? 60;

                throw ValidationException::withMessages([
                    Fortify::username() => [
                        "Too many login attempts. Account locked for {$seconds} seconds. Please retry after {$seconds} seconds.",
                    ],
                ])->status(429);
            });
        });

        // Registration throttling: 3 percobaan per IP per 600 detik (10 menit)
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(10, 3)->by($request->ip())->response(function (Request $request, array $headers) {
                $seconds = $headers['Retry-After'] ?? 600;

                throw ValidationException::withMessages([
                    'email' => [
                        "Too many registration attempts. Please retry after {$seconds} seconds.",
                    ],
                ])->status(429);
            });
        });
    }
}
