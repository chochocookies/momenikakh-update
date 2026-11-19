<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Part of the `public` middleware group (undangan.): no session, no cookies, no CSRF token. The pages are cached at the
 * edge and shared between guests, so a cookie on them would defeat the cache and leak state. Guest actions (RSVP, wishes,
 * the opened beacon) are protected by throttling and Turnstile instead of a CSRF token.
 *
 * Views of the other surfaces expect an `$errors` bag from the session; give them an empty one here.
 */
final class StatelessPublic
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('errors', new ViewErrorBag);

        return $next($request);
    }
}
