<?php

declare(strict_types=1);

namespace App\Http\Controllers\Share;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateRenderer;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * The public invitation page: undangan.{domain}/{slug} (PUB-01). Only published invitations are served; the friendly
 * 404/410 pages, caching and meta tags come with PUB-02, PUB-03 and PUB-08.
 */
final class InvitationController extends Controller
{
    public function show(string $slug, InvitationViewModelBuilder $builder, TemplateRenderer $renderer): View
    {
        $invitation = Invitation::query()->published()->where('slug', $slug)->first() ?? abort(404);

        return $renderer->view($builder->fromModel($invitation));
    }
}
