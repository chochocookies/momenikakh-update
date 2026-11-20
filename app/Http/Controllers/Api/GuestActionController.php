<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Guest\Enums\Attendance;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Guest\Services\TextCleaner;
use App\Domain\Guest\Services\TurnstileVerifier;
use App\Domain\Invitation\Models\Invitation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * PUB-09 and PUB-10: what a guest can send from the invitation page, RSVP and wishes. The surface has no session or
 * CSRF token (the page is cached at the edge), so these routes are protected by throttling, a honeypot field and
 * Turnstile. They answer JSON only. Guest text is stored as typed (cleaned of control characters) and is escaped
 * wherever it is shown: Blade escapes it, and the page script writes it with textContent.
 */
final class GuestActionController extends Controller
{
    public const WISHES_PER_PAGE = 10;

    public function rsvp(Request $request, string $slug, TurnstileVerifier $turnstile): JsonResponse
    {
        $invitation = $this->open($slug, 'rsvp_enabled');
        if ($request->filled('website')) {
            return $this->fakeOk(); // a bot filled the hidden field: pretend, store nothing
        }

        $data = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'attendance' => ['required', Rule::enum(Attendance::class)],
            'pax' => ['nullable', 'integer', 'between:1,10'],
            'note' => ['nullable', 'string', 'max:300'],
            'g' => ['nullable', 'string', 'size:12', 'alpha_num:ascii'],
        ], [], ['name' => 'Nama', 'attendance' => 'Kehadiran', 'pax' => 'Jumlah tamu', 'note' => 'Catatan'])->validate();

        $this->guardTurnstile($request, $turnstile);

        $attributes = [
            'name' => TextCleaner::singleLine($data['name']),
            'attendance' => $data['attendance'],
            'pax' => $data['attendance'] === Attendance::No->value ? 1 : (int) ($data['pax'] ?? 1),
            'note' => $this->clean($data['note'] ?? null),
            'ip_hash' => $this->ipHash($request),
        ];
        $guest = $this->guest($invitation, $data['g'] ?? null);

        // One answer per known guest (a changed mind updates it); anonymous answers are separate rows.
        // guest_id is not mass-assignable on purpose, so it is set here, from the verified token only.
        $rsvp = ($guest !== null ? $invitation->rsvps()->where('guest_id', $guest->id)->first() : null) ?? new Rsvp;
        $rsvp->fill($attributes);
        $rsvp->forceFill(['invitation_id' => $invitation->id, 'guest_id' => $guest?->id])->save();

        return response()->json(['message' => 'Terima kasih. Konfirmasimu sudah kami terima.'], 201);
    }

    public function wishes(string $slug): JsonResponse
    {
        $invitation = $this->open($slug, 'wishes_enabled');
        $page = $invitation->messages()->where('is_visible', true)->orderByDesc('id')->cursorPaginate(self::WISHES_PER_PAGE, ['id', 'name', 'message', 'created_at']);

        return response()->json([
            'data' => $page->getCollection()->map(fn (GuestMessage $m) => ['name' => $m->name, 'message' => $m->message, 'at' => $m->created_at?->toIso8601String()])->all(),
            'next' => $page->nextCursor()?->encode(),
        ]);
    }

    public function storeWish(Request $request, string $slug, TurnstileVerifier $turnstile): JsonResponse
    {
        $invitation = $this->open($slug, 'wishes_enabled');
        if ($request->filled('website')) {
            return $this->fakeOk();
        }

        $data = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:300'],
            'g' => ['nullable', 'string', 'size:12', 'alpha_num:ascii'],
        ], [], ['name' => 'Nama', 'message' => 'Ucapan'])->validate();

        $this->guardTurnstile($request, $turnstile);

        $message = $this->clean($data['message']);
        if ($message === null) {
            return response()->json(['message' => 'Ucapan wajib diisi.', 'errors' => ['message' => ['Ucapan wajib diisi.']]], 422);
        }
        $wish = new GuestMessage(['name' => TextCleaner::singleLine($data['name']), 'message' => $message, 'is_visible' => true]);
        $wish->forceFill(['invitation_id' => $invitation->id, 'guest_id' => $this->guest($invitation, $data['g'] ?? null)?->id])->save();

        return response()->json(['message' => 'Terima kasih. Ucapanmu sudah kami terima.'], 201);
    }

    /** A published invitation whose setting (rsvp_enabled or wishes_enabled) is not switched off, else 404. */
    private function open(string $slug, string $setting): Invitation
    {
        $invitation = Invitation::query()->published()->where('slug', $slug)->first();
        abort_if($invitation === null || ($invitation->settings[$setting] ?? true) === false, 404);

        return $invitation;
    }

    private function guest(Invitation $invitation, ?string $token): ?Guest
    {
        return $token === null ? null : $invitation->guests()->where('token', $token)->first();
    }

    private function guardTurnstile(Request $request, TurnstileVerifier $turnstile): void
    {
        abort_unless($turnstile->passes($request->input('cf-turnstile-response'), $request->ip()), 422, 'Verifikasi gagal. Muat ulang halaman, lalu coba lagi.');
    }

    /** Multi-line text: control characters out (except newlines), runs of blank lines collapsed, trimmed. */
    private function clean(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }
        $text = preg_replace('/[^\P{Cc}\n]|[\x{200B}\x{2060}-\x{2064}\x{2066}-\x{2069}\x{202A}-\x{202E}\x{FEFF}]/u', '', str_replace("\r\n", "\n", $text)) ?? $text;
        $text = trim((string) preg_replace('/\n{3,}/', "\n\n", $text));

        return $text === '' ? null : $text;
    }

    private function ipHash(Request $request): string
    {
        return hash('sha256', (string) config('app.key').'|'.$request->ip());
    }

    private function fakeOk(): JsonResponse
    {
        return response()->json(['message' => 'Terima kasih.'], 201);
    }
}
