<?php

declare(strict_types=1);

namespace App\Http\Controllers\Share;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\TemplateManifest;
use App\Domain\Templates\TemplateRegistry;
use App\Domain\Templates\TemplateRenderer;
use App\Domain\Templates\TemplateSampleData;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Template preview on the public surface (PUB-04), filled with fictional sample data:
 *   /preview/{template}         wrapper page: phone or desktop frame, event type switch
 *   /preview/{template}/frame   the template itself, embedded by the wrapper (and by the dashboard editor later)
 */
final class PreviewController extends Controller
{
    public const MODES = ['phone', 'desktop'];

    public function show(Request $request, string $template, TemplateRegistry $registry): View
    {
        $manifest = $this->manifest($registry, $template);
        $event = $this->event($request, $manifest);
        $mode = in_array($request->query('mode'), self::MODES, true) ? (string) $request->query('mode') : 'phone';

        return view('preview.show', [
            'manifest' => $manifest,
            'event' => $event,
            'mode' => $mode,
            'frameUrl' => route('public.preview.frame', ['template' => $manifest->slug, 'event' => $event->value]),
            'eventTypes' => $manifest->events,
            'modes' => self::MODES,
        ]);
    }

    public function frame(Request $request, string $template, TemplateRegistry $registry, TemplateSampleData $samples, TemplateRenderer $renderer): View
    {
        $manifest = $this->manifest($registry, $template);
        $event = $this->event($request, $manifest);

        return $renderer->view($samples->viewModel($event, $manifest->slug));
    }

    private function manifest(TemplateRegistry $registry, string $slug): TemplateManifest
    {
        return $registry->find($slug) ?? abort(404);
    }

    /** The requested event type when the template supports it; the template's first one when none is requested. */
    private function event(Request $request, TemplateManifest $manifest): EventType
    {
        $requested = $request->query('event');
        if ($requested === null) {
            return $manifest->events[0];
        }

        $type = is_string($requested) ? EventType::tryFrom($requested) : null;

        return $type !== null && $manifest->supportsEvent($type) ? $type : abort(404);
    }
}
