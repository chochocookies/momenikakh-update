<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Billing\Entitlements;
use App\Domain\Guest\Export\CsvExporter;
use App\Domain\Guest\Export\MessageExporter;
use App\Domain\Guest\Export\RsvpExporter;
use App\Domain\Invitation\Models\Invitation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** GST-03. Owner only, gated by the `export_guests` ability, streamed, formula-injection safe. */
final class GuestExportController extends Controller
{
    public function rsvps(Request $request, Invitation $invitation, RsvpExporter $exporter, Entitlements $entitlements): StreamedResponse
    {
        return $this->stream($request, $invitation, $entitlements, RsvpExporter::HEADERS, $exporter->rows($invitation), $exporter->filename($invitation));
    }

    public function messages(Request $request, Invitation $invitation, MessageExporter $exporter, Entitlements $entitlements): StreamedResponse
    {
        return $this->stream($request, $invitation, $entitlements, MessageExporter::HEADERS, $exporter->rows($invitation), $exporter->filename($invitation));
    }

    /** @param list<string> $headers */
    private function stream(Request $request, Invitation $invitation, Entitlements $entitlements, array $headers, iterable $rows, string $filename): StreamedResponse
    {
        $this->authorize('manageGuests', $invitation);
        abort_unless($entitlements->can($invitation, 'export_guests'), 403, 'Ekspor data tersedia untuk paket tertentu.');

        // "excel-id" = semicolon, which Excel with Indonesian regional settings expects.
        $delimiter = $request->query('format') === 'excel-id' ? ';' : (config('guests.export.delimiter') === ';' ? ';' : ',');

        return response()->streamDownload(function () use ($delimiter, $headers, $rows): void {
            $out = fopen('php://output', 'w');
            (new CsvExporter($delimiter))->write($out, $headers, $rows);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
