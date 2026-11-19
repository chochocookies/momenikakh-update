<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headers for the template preview (PUB-04). Previews are never indexed. The frame page may be embedded by the
 * wrapper page on the same origin only; every other public page keeps `frame-ancestors 'none'`.
 *
 * Use as `preview.headers:frame` on the frame route and `preview.headers` on the wrapper.
 */
final class PreviewHeaders
{
    public function handle(Request $request, Closure $next, string $mode = 'wrapper'): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        if ($mode === 'frame') {
            $directives = (array) config('security.surfaces.public.csp');
            $directives['frame-ancestors'] = "'self'";

            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Content-Security-Policy', collect($directives)
                ->map(fn (string $value, string $directive) => "{$directive} {$value}")
                ->implode('; '));
        }

        return $response;
    }
}
