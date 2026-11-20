<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Account\AccountDataExporter;
use App\Domain\Account\AccountEraser;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** DSH-10: the owner's account page, with the two rights the UU PDP gives: a copy of the data and its deletion. */
final class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('dashboard.account', ['user' => $request->user(), 'invitations' => $request->user()->invitations()->count()]);
    }

    public function export(Request $request, AccountDataExporter $exporter): StreamedResponse
    {
        $data = $exporter->export($request->user());

        return response()->streamDownload(
            fn () => print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'data-momenikah-'.now()->format('Ymd').'.json',
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public function destroy(Request $request, AccountEraser $eraser): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
            'confirm' => ['required', 'in:HAPUS'],
        ], [
            'password.current_password' => 'Kata sandi tidak cocok.',
            'confirm.in' => 'Ketik HAPUS untuk melanjutkan.',
        ], ['password' => 'Kata sandi', 'confirm' => 'Konfirmasi']);

        $user = $request->user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $eraser->erase($user);

        return redirect()->away(request()->getScheme().'://'.config('surfaces.marketing.domain'));
    }
}
