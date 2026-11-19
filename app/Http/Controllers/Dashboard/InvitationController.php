<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\PlanCatalog;
use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationCreator;
use App\Domain\Templates\TemplateRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\StoreInvitationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** DSH-02: the create wizard (event type, then template, title and start mode) and the invitation page. */
final class InvitationController extends Controller
{
    /** Step 1 lists the event types; step 2 (?event=wedding) lists the templates for that type. */
    public function create(Request $request, EventTypeRegistry $events, TemplateRegistry $templates): View
    {
        $this->authorize('create', Invitation::class);

        // Only event types that have at least one template can be started.
        $available = array_filter($events->all(), fn (EventTypeDefinition $d) => $templates->forEvent($d->type) !== []);

        if (! $request->has('event')) {
            return view('dashboard.invitations.create', ['available' => $available, 'definition' => null, 'templates' => []]);
        }

        $type = EventType::tryFrom((string) $request->query('event'));
        if ($type === null || ! isset($available[$type->value])) {
            abort(404);
        }

        return view('dashboard.invitations.create', [
            'available' => $available,
            'definition' => $events->for($type),
            'templates' => $templates->forEvent($type),
            'thumbs' => array_map(
                fn ($slug) => $templates->image($slug, 'thumb.webp') !== null ? route('dashboard.template.image', ['template' => $slug, 'file' => 'thumb.webp']) : null,
                array_combine($templates->slugs(), $templates->slugs()),
            ),
        ]);
    }

    public function store(StoreInvitationRequest $request, InvitationCreator $creator): RedirectResponse
    {
        $data = $request->validated();

        $invitation = $creator->create(
            $request->user(),
            EventType::from($data['event_type']),
            $data['template_slug'],
            trim($data['title']),
            $data['start'] === 'sample',
        );

        return redirect()->route('dashboard.invitations.show', $invitation)->with('toast', 'Undangan dibuat. Isi datanya, lalu lihat pratinjaunya.');
    }

    public function show(Invitation $invitation, EventTypeRegistry $events, TemplateRegistry $templates, Entitlements $entitlements, PlanCatalog $plans, GuestLinkBuilder $links): View
    {
        $this->authorize('view', $invitation);

        return view('dashboard.invitations.show', [
            'invitation' => $invitation,
            'eventLabel' => $events->for($invitation->event_type)->label,
            'template' => $templates->find((string) $invitation->template_slug),
            'canPublish' => $entitlements->can($invitation, 'publish'),
            'plans' => $plans->summary(),
            'publicUrl' => $invitation->isPublished() ? $links->plain($invitation->slug) : null,
        ]);
    }
}
