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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** DSH-10: the owner's account page, with the two rights the UU PDP gives: a copy of the data and its deletion. */
final class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('dashboard.account', ['user' => $request->user(), 'invitations' => $request->user()->invitations()->count()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ]);

        $request->user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('dashboard.account')->with('toast', 'Profil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard.account')->with('toast', 'Kata sandi diperbarui.');
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
