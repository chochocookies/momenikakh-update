<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Audit\AuditLogger;
use App\Domain\Audit\Enums\AuditAction;
use App\Domain\Guest\Exceptions\GuestLimitReached;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Guest\Services\GuestService;
use App\Domain\Guest\Services\ShareMessageBuilder;
use App\Domain\Invitation\Models\Invitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guests\StoreGuestRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class GuestController extends Controller
{
    public function index(Request $request, Invitation $invitation, GuestLinkBuilder $links, ShareMessageBuilder $messages): View
    {
        $this->authorize('manageGuests', $invitation);

        $q = trim((string) $request->query('q', ''));
        $guests = $invitation->guests()
            // "!" is the LIKE escape character because it behaves the same on MySQL and SQLite (tests).
            ->when($q !== '', fn ($query) => $query->whereRaw("guests.name like ? escape '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%']))
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        $rows = $guests->getCollection()->map(function (Guest $g) use ($invitation, $links, $messages) {
            $url = $links->forGuest($invitation->slug, $g->name, $g->token);
            $text = $messages->build($invitation->settings['share_message'] ?? null, $g->name, $invitation->title, $url);

            return ['guest' => $g, 'url' => $url, 'whatsapp' => $links->whatsapp($g->phone, $text)];
        });

        return view('dashboard.guests.index', [
            'invitation' => $invitation, 'guests' => $guests, 'rows' => $rows, 'q' => $q,
            'total' => $invitation->guests()->count(), 'opened' => $invitation->guests()->whereNotNull('opened_at')->count(),
        ]);
    }

    public function store(StoreGuestRequest $request, Invitation $invitation, GuestService $service): RedirectResponse
    {
        try {
            $service->add($invitation, $request->validated());
        } catch (GuestLimitReached $e) {
            return back()->withErrors(['name' => $e->getMessage()])->withInput();
        }

        return back()->with('toast', 'Tamu ditambahkan.');
    }

    public function destroy(Request $request, Invitation $invitation, Guest $guest, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('delete', $guest);
        // The log keeps ids only, never the guest's name or phone number.
        $audit->record(AuditAction::GuestDeleted, $request->user(), $guest, $invitation->id);
        $guest->delete();

        return back()->with('toast', 'Tamu dihapus.');
    }

    public function regenerateToken(Invitation $invitation, Guest $guest, GuestService $service): RedirectResponse
    {
        $this->authorize('update', $guest);
        $service->regenerateToken($guest);

        return back()->with('toast', 'Tautan tamu diperbarui. Tautan lama tidak lagi mencatat status dibuka.');
    }
}
