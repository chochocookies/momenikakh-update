<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateRenderer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * DSH-04: the owner's own draft, rendered with the real template and the real data, for the iframe in the editor.
 * Same origin as the editor (the route sends SAMEORIGIN), never cached, never indexed. Forms are disabled (preview mode).
 */
final class DraftPreviewController extends Controller
{
    public function __invoke(Invitation $invitation, InvitationViewModelBuilder $builder, TemplateRenderer $renderer): Response
    {
        $this->authorize('view', $invitation);

        return response($renderer->html($builder->fromModel($invitation, preview: true)))
            ->header('Cache-Control', 'no-store, private');
    }
}
