<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSurfaceHeader
{
    public function handle(Request $request, Closure $next, string $surface): Response
    {
        $response = $next($request);

        $response->headers->set('X-Surface', $surface);

        return $response;
    }
}
