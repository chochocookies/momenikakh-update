<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Guest\Services\VisitRecorder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** PUB-06: the page's own "seen" beacon (view, open). Always 204, so it cannot be used to probe which slugs exist. */
final class VisitController extends Controller
{
    public function __invoke(Request $request, string $slug, VisitRecorder $recorder): Response
    {
        $recorder->record($slug, (string) $request->input('event', ''), (string) $request->ip(), $request->userAgent());

        return response()->noContent();
    }
}
