<?php

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset rate limiters before each test
    RateLimiter::clear('login');
    RateLimiter::clear('register');
});

test('6 consecutive failed login attempts trigger rate limiting', function () {
    $user = User::factory()->create([
        'password' => bcrypt('Password123!'),
        'email_verified_at' => now(),
    ]);

    // Perform 5 failed login attempts
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);
        
        $response->assertSessionHasErrors('email');
        expect($response->status())->toBe(302);
    }

    // 6th attempt should be rate limited (429 Too Many Requests)
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    // Rate limiter should block with 429 status
    expect($response->status())->toBe(429);
    
    // Verify error message indicates rate limiting
    $response->assertSessionHasErrors('email');
    $errors = $response->getSession()->get('errors');
    expect($errors->get('email'))->toContain('Too many login attempts');
    expect($errors->get('email'))->toContain('locked');
})->group('auth');

test('rate-limited users cannot login until lockout expires', function () {
    $user = User::factory()->create([
        'password' => bcrypt('Password123!'),
        'email_verified_at' => now(),
    ]);

    // Trigger rate limiting by making 6 failed attempts
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);
    }

    // Verify user is locked out (cannot login even with correct password)
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(429);
    expect($this->isGuest())->toBeTrue();

    // Simulate time passing - 59 seconds (lockout NOT expired yet)
    $this->travel(59)->seconds();

    // Should still be locked out
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(429);
    expect($this->isGuest())->toBeTrue();

    // Simulate time passing - 2 more seconds (total 61 seconds, lockout expired)
    $this->travel(2)->seconds();

    // Now login should succeed
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(302);
    $this->assertAuthenticatedAs($user);
})->group('auth');

test('successful authentication after rate limit expiry works correctly', function () {
    Event::fake();
    
    $user = User::factory()->create([
        'password' => bcrypt('Password123!'),
        'email_verified_at' => now(),
    ]);

    // Trigger rate limiting
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);
    }

    // Wait for lockout to expire (61 seconds)
    $this->travel(61)->seconds();

    // Login should succeed and fire Login event
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(302);
    expect($response->headers->has('Location'))->toBeTrue();
    
    $this->assertAuthenticatedAs($user);
    
    // Verify login event was fired
    Event::assertDispatched(Login::class);
})->group('auth');

test('rate limiting uses correct throttle key (email + IP combination)', function () {
    $user1 = User::factory()->create([
        'password' => bcrypt('Password123!'),
        'email_verified_at' => now(),
    ]);

    $user2 = User::factory()->create([
        'password' => bcrypt('Password123!'),
        'email_verified_at' => now(),
    ]);

    // Make 6 failed attempts for user1
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', [
            'email' => $user1->email,
            'password' => 'WrongPassword!',
        ]);
    }

    // user2 should still be able to login (different throttle key)
    $response = $this->post('/login', [
        'email' => $user2->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(302);
    $this->assertAuthenticatedAs($user2);
})->group('auth');

test('rate limiting resets after lockout period expires', function () {
    $user = User::factory()->create([
        'password' => bcrypt('Password123!'),
        'email_verified_at' => now(),
    ]);

    // Trigger rate limiting
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);
    }

    // Wait for lockout to expire
    $this->travel(61)->seconds();

    // Should be able to make login attempts again
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(302);
    $this->assertAuthenticatedAs($user);
    
    // After successful login, the rate limit counter should reset
    // Make another 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);
        expect($response->status())->toBe(302);
    }

    // 6th attempt should be rate limited again
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    expect($response->status())->toBe(429);
})->group('auth');
