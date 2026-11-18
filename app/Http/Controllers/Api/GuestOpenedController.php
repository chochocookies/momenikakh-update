<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Guest\Services\GuestOpenedRecorder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public beacon from the invitation page (navigator.sendBeacon). Always answers 204 so it cannot be used to
 * probe which tokens exist. Exclude this route from CSRF (the page is cached at the edge and cannot embed a token)
 * and keep the throttle.
 */
final class GuestOpenedController extends Controller
{
    public function __invoke(Request $request, string $slug, GuestOpenedRecorder $recorder): Response
    {
        $recorder->record($slug, (string) $request->input('g', ''));

        return response()->noContent();
    }
}
