<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Account\Onboarding;
use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\TemplateRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** DSH-01: the owner's invitations. The list is always narrowed with ownedBy(), so nobody sees someone else's. */
final class DashboardController extends Controller
{
    public const PER_PAGE = 12;

    public function index(Request $request, EventTypeRegistry $events, TemplateRegistry $templates, Entitlements $entitlements, Onboarding $onboarding): View
    {
        $user = $request->user();
        $invitations = Invitation::query()->ownedBy($user)->latest('id')->paginate(self::PER_PAGE);

        return view('dashboard.index', [
            'user' => $user,
            'onboarding' => $onboarding->steps($user),
            'invitations' => $invitations,
            'eventLabels' => array_map(fn ($definition) => $definition->label, $events->all()),
            'templateNames' => array_map(fn ($manifest) => $manifest->name, $templates->all()),
            // One answer per invitation, always through Entitlements: a sample cannot publish, a paid one can.
            'paid' => $invitations->getCollection()->mapWithKeys(fn (Invitation $i) => [$i->id => $entitlements->can($i, 'publish')])->all(),
        ]);
    }

    public function dismissOnboarding(Request $request, Onboarding $onboarding): RedirectResponse
    {
        $onboarding->dismiss($request->user());

        return redirect()->route('dashboard');
    }
}
