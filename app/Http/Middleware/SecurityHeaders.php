<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Global security headers (Task 9.1)
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 2. Environment-conditional HSTS (Production only)
        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // 3. Deteksi surface aktif (dari header X-Surface atau host domain)
        $surface = $this->resolveSurface($request, $response);
        $surfaceConfig = config("security.surfaces.{$surface}", config('security.surfaces.marketing'));

        // PUB-08: invitations are for people holding the link, never for search engines. The page also carries <meta name=robots>.
        if ($surface === 'public' && ! $response->headers->has('X-Robots-Tag')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        // 4. Surface-specific X-Frame-Options (Task 9.2)
        // A route may set its own value first (the preview frame uses SAMEORIGIN); never overwrite that.
        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', $surfaceConfig['frame_options']);
        }

        // 5. Surface-specific Content-Security-Policy (Task 9.3 - 9.5)
        // Only set CSP if not already present (check both original and normalized header names)
        if (! $response->headers->has('Content-Security-Policy') && ! $response->headers->has('content-security-policy')) {
            $cspHeader = collect($surfaceConfig['csp'])
                ->map(fn (string $value, string $directive) => "{$directive} {$value}")
                ->implode('; ');

            $response->headers->set('Content-Security-Policy', $cspHeader);
        }

        return $response;
    }

    protected function resolveSurface(Request $request, Response $response): string
    {
        $headerSurface = $response->headers->get('X-Surface');
        if ($headerSurface && in_array($headerSurface, ['marketing', 'app', 'public'], true)) {
            return $headerSurface;
        }

        $host = $request->getHost();

        if ($host === config('surfaces.app.domain', 'app.momenikah.test')) {
            return 'app';
        }

        if ($host === config('surfaces.public.domain', 'undangan.momenikah.test')) {
            return 'public';
        }

        return 'marketing';
    }
}
