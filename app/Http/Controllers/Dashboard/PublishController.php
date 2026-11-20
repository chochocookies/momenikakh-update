<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvalidTransition;
use App\Domain\Invitation\Services\InvitationLifecycle;
use App\Domain\Invitation\Services\InvitationReadiness;
use App\Domain\Invitation\Services\PublishRequiresPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** DSH-05: publish (only when complete and paid) and unpublish (back to draft). */
final class PublishController extends Controller
{
    public function publish(Request $request, Invitation $invitation, InvitationLifecycle $lifecycle, InvitationReadiness $readiness): RedirectResponse
    {
        $this->authorize('publish', $invitation);
        $back = redirect()->route('dashboard.invitations.show', $invitation);

        if (! $readiness->check($invitation)['ready']) {
            return $back->with('alert', 'Lengkapi datanya dulu sebelum menerbitkan.');
        }

        try {
            $lifecycle->publish($invitation, null, $request->user());
        } catch (PublishRequiresPlan) {
            return $back->with('alert', 'Undangan ini masih sample gratis. Paket Template diperlukan untuk menerbitkannya.');
        } catch (InvalidTransition) {
            return $back->with('alert', 'Undangan dengan status ini tidak bisa diterbitkan.');
        }

        return $back->with('toast', 'Undanganmu sudah terbit. Bagikan tautannya.');
    }

    public function unpublish(Request $request, Invitation $invitation, InvitationLifecycle $lifecycle): RedirectResponse
    {
        $this->authorize('publish', $invitation);

        try {
            $lifecycle->unpublish($invitation, $request->user());
        } catch (InvalidTransition) {
            return redirect()->route('dashboard.invitations.show', $invitation)->with('alert', 'Undangan ini tidak sedang terbit.');
        }

        return redirect()->route('dashboard.invitations.show', $invitation)->with('toast', 'Undangan kembali menjadi draf. Tautannya tidak lagi terbuka.');
    }
}
