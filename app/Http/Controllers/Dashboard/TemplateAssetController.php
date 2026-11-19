<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Templates\TemplateRegistry;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Serves thumb.webp / preview.webp of a registered template and nothing else from the template folders. */
final class TemplateAssetController extends Controller
{
    public function __invoke(string $template, string $file, TemplateRegistry $registry): BinaryFileResponse
    {
        $path = $registry->image($template, $file) ?? abort(404);

        return response()->file($path, ['Content-Type' => 'image/webp', 'Cache-Control' => 'public, max-age=86400']);
    }
}
