<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Guest\Exceptions\GuestLimitReached;
use App\Domain\Guest\Export\CsvExporter;
use App\Domain\Guest\Services\GuestImporter;
use App\Domain\Invitation\Models\Invitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guests\ImportGuestsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Two steps: upload -> preview (nothing written) -> confirm. The confirmed rows are re-parsed from the stored
 * file on the server; the browser never sends the rows back, so they cannot be tampered with.
 */
final class GuestImportController extends Controller
{
    private const DIR = 'guest-imports';

    public function preview(ImportGuestsRequest $request, Invitation $invitation, GuestImporter $importer): View|RedirectResponse
    {
        $id = (string) Str::uuid();
        $path = $request->file('file')->storeAs(self::DIR, $id.'.csv', 'local');

        $report = $importer->preview($invitation, Storage::disk('local')->path($path));
        if ($report->fatal !== null) {
            Storage::disk('local')->delete($path);

            return back()->withErrors(['file' => $report->fatal]);
        }
        session()->put("guest_import.{$id}", ['invitation' => $invitation->id, 'user' => $request->user()->id, 'at' => now()->timestamp]);

        return view('dashboard.guests.import-preview', ['invitation' => $invitation, 'report' => $report, 'importId' => $id]);
    }

    public function commit(Request $request, Invitation $invitation, string $import, GuestImporter $importer): RedirectResponse
    {
        $this->authorize('manageGuests', $invitation);
        $meta = Str::isUuid($import) ? session("guest_import.{$import}") : null;
        $ttl = (int) config('guests.import.ttl_minutes', 60) * 60;

        abort_unless(
            is_array($meta) && $meta['invitation'] === $invitation->id && $meta['user'] === $request->user()->id && now()->timestamp - $meta['at'] <= $ttl,
            410,
            'Pratinjau impor sudah kedaluwarsa. Unggah ulang berkasnya.',
        );

        $path = self::DIR.'/'.$import.'.csv';
        try {
            $report = $importer->preview($invitation, Storage::disk('local')->path($path));
            $count = $importer->commit($invitation, $report);
        } catch (GuestLimitReached $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        } finally {
            Storage::disk('local')->delete($path);
            session()->forget("guest_import.{$import}");
        }

        return redirect()->route('dashboard.guests.index', $invitation)->with('toast', "{$count} tamu diimpor.");
    }

    public function cancel(Request $request, Invitation $invitation, string $import): RedirectResponse
    {
        $this->authorize('manageGuests', $invitation);
        if (Str::isUuid($import)) {
            Storage::disk('local')->delete(self::DIR.'/'.$import.'.csv');
            session()->forget("guest_import.{$import}");
        }

        return redirect()->route('dashboard.guests.index', $invitation);
    }

    /** CSV template with fictional sample rows. */
    public function template(Invitation $invitation): StreamedResponse
    {
        $this->authorize('manageGuests', $invitation);

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            (new CsvExporter(','))->write($out, ['nama', 'kelompok', 'telepon'], [
                ['Budi Santoso', 'Keluarga', '081200000001'],
                ['Siti Rahma', 'Teman kantor', ''],
            ]);
            fclose($out);
        }, 'template-tamu.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
