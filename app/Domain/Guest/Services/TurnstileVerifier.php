<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * PUB-09: checks a Cloudflare Turnstile token. Not configured (no secret) means "off": local work and CI have no keys.
 * When it is on, a missing, wrong or unverifiable token fails closed.
 */
final class TurnstileVerifier
{
    private const ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function enabled(): bool
    {
        return (string) config('services.turnstile.secret') !== '';
    }

    public function passes(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }
        if ($token === null || $token === '' || strlen($token) > 2048) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::ENDPOINT, [
                'secret' => config('services.turnstile.secret'), 'response' => $token, 'remoteip' => $ip,
            ]);
        } catch (Throwable) {
            return false;
        }

        return $response->successful() && $response->json('success') === true;
    }
}
