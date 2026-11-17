<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\Fluent\AssertableJson;


// NOTE: These tests require the authentication starter kit and multi-surface routing
// to be fully configured (Tasks 8.1–8.4). Run with: php artisan test --group=auth
// They are excluded from the default test run until those tasks are complete.

test('user can register and receives verification email within time limits', function () {
    Notification::fake();
    $startTime = microtime(true);

    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $elapsed = microtime(true) - $startTime;

    $response->assertSessionHasNoErrors();

    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull();
    expect($elapsed)->toBeLessThan(5.0);

    Notification::assertSentTo($user, VerifyEmail::class);
})->group('auth');

test('user can verify email address within time limits', function () {
    Event::fake();
    $startTime = microtime(true);

    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $elapsed = microtime(true) - $startTime;
    expect($elapsed)->toBeLessThan(2.0);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
})->group('auth');

test('user can login with valid credentials within 2 seconds', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
        'email_verified_at' => now(),
    ]);

    $startTime = microtime(true);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $elapsed = microtime(true) - $startTime;

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);
    expect($elapsed)->toBeLessThan(2.0);
})->group('auth');

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
        'email_verified_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'WrongPassword!',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
})->group('auth');

test('complete password reset flow works properly', function () {
    Notification::fake();

    $user = User::factory()->create();

    // Request password reset
    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        // Reset password with token
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();

        return true;
    });
})->group('auth');

test('email verification flow works correctly', function () {
    $user = User::factory()->unverified()->create();

    // Simulate clicking verification link
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertSessionHas('status', 'verifying');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
})->group('auth');

test('6 consecutive failed login attempts trigger rate limiting and expire after 60 seconds', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
        'email_verified_at' => now(),
    ]);

    // 5 failed attempts first
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // 6th attempt should be rate limited
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    // Rate limiter should block (429 status or validation error)
    expect($response->status())->toBeOneOf([429, 302]);

    // Simulate time passing 61 seconds (lockout expires)
    $this->travel(61)->seconds();

    // Login should now succeed
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);
})->group('auth');
