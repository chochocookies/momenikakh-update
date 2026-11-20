<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Audit\AuditLogger;
use App\Domain\Audit\Enums\AuditAction;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Invitation\Models\Invitation;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** PUB-11: what guests sent (RSVP and wishes) and the owner's moderation of wishes: hide, show again, delete. Each action is audited. */
final class ResponseController extends Controller
{
    public function index(Invitation $invitation): View
    {
        $this->authorize('moderate', $invitation);

        $rsvps = $invitation->rsvps();
        $count = fn (string $attendance) => (clone $rsvps)->where('attendance', $attendance);

        return view('dashboard.responses.index', [
            'invitation' => $invitation,
            'summary' => [
                'yes' => $count('yes')->count(), 'yes_pax' => (int) $count('yes')->sum('pax'),
                'maybe' => $count('maybe')->count(), 'no' => $count('no')->count(),
            ],
            'rsvps' => $invitation->rsvps()->latest('id')->paginate(25, ['*'], 'rsvp_page')->withQueryString(),
            'messages' => $invitation->messages()->latest('id')->paginate(25, ['*'], 'wish_page')->withQueryString(),
        ]);
    }

    public function hide(Request $request, Invitation $invitation, GuestMessage $message, AuditLogger $audit): RedirectResponse
    {
        return $this->set($request, $invitation, $message, $audit, false);
    }

    public function show(Request $request, Invitation $invitation, GuestMessage $message, AuditLogger $audit): RedirectResponse
    {
        return $this->set($request, $invitation, $message, $audit, true);
    }

    public function destroy(Request $request, Invitation $invitation, GuestMessage $message, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('moderate', $invitation);
        $message->delete();
        $audit->record(AuditAction::MessageDeleted, $request->user(), null, $invitation->id);

        return back()->with('toast', 'Ucapan dihapus.');
    }

    private function set(Request $request, Invitation $invitation, GuestMessage $message, AuditLogger $audit, bool $visible): RedirectResponse
    {
        $this->authorize('moderate', $invitation);
        $message->forceFill(['is_visible' => $visible])->save();
        $audit->record($visible ? AuditAction::MessageShown : AuditAction::MessageHidden, $request->user(), $message, $invitation->id);

        return back()->with('toast', $visible ? 'Ucapan ditampilkan lagi.' : 'Ucapan disembunyikan dari undangan.');
    }
}
