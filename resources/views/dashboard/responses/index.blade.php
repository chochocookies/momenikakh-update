@php
    $attendanceLabel = ['yes' => 'Hadir', 'maybe' => 'Masih ragu', 'no' => 'Tidak hadir'];
    $answers = $summary['yes'] + $summary['maybe'] + $summary['no'];
@endphp
<x-layouts.app :title="'Jawaban tamu: '.$invitation->title">
    @if (session('toast'))
        <div hidden data-toast="{{ session('toast') }}" data-tone="success"></div>
    @endif

    <div class="grid gap-8">
        <header class="grid gap-2">
            <p><a class="inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('dashboard.invitations.show', $invitation) }}">Kembali ke undangan</a></p>
            <h1 class="font-serif text-4xl font-semibold text-ink">Jawaban tamu</h1>
            <p class="tabular text-muted">
                {{ $invitation->title }}:
                @if ($answers === 0)
                    belum ada konfirmasi kehadiran.
                @else
                    {{ $answers }} konfirmasi. {{ $summary['yes'] }} hadir ({{ $summary['yes_pax'] }} orang), {{ $summary['maybe'] }} masih ragu, {{ $summary['no'] }} tidak hadir.
                @endif
            </p>
        </header>

        <section aria-labelledby="rsvp-title" class="grid gap-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 id="rsvp-title" class="font-serif text-2xl font-semibold text-ink">Konfirmasi kehadiran</h2>
                <x-ui.button variant="secondary" :href="route('dashboard.guests.export.rsvps', $invitation)">Ekspor CSV</x-ui.button>
            </div>
            @if ($rsvps->isEmpty())
                <x-ui.empty-state title="Belum ada konfirmasi" icon="users">Konfirmasi muncul di sini setelah tamu mengisi formulir di undanganmu.</x-ui.empty-state>
            @else
                <div class="relative overflow-x-auto rounded-2xl border border-ink/10 bg-white" tabindex="0" role="region" aria-label="Konfirmasi kehadiran dari tamu">
                    <table class="w-full min-w-[32rem] text-left">
                        <caption class="sr-only">Konfirmasi kehadiran dari tamu</caption>
                        <thead class="bg-rose-light text-ink">
                            <tr>
                                <th scope="col" class="px-4 py-3">Nama</th>
                                <th scope="col" class="px-4 py-3">Kehadiran</th>
                                <th scope="col" class="px-4 py-3">Orang</th>
                                <th scope="col" class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/10">
                            @foreach ($rsvps as $rsvp)
                                <tr>
                                    <th scope="row" class="px-4 py-3 font-semibold text-ink">{{ $rsvp->name }}</th>
                                    <td class="px-4 py-3">{{ $attendanceLabel[$rsvp->attendance->value] }}</td>
                                    <td class="px-4 py-3">{{ $rsvp->pax }}</td>
                                    <td class="px-4 py-3 text-muted">{{ $rsvp->note ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-ui.pagination :paginator="$rsvps" />
            @endif
        </section>

        <section aria-labelledby="wishes-title" class="grid gap-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 id="wishes-title" class="font-serif text-2xl font-semibold text-ink">Ucapan dan doa</h2>
                <x-ui.button variant="secondary" :href="route('dashboard.guests.export.messages', $invitation)">Ekspor CSV</x-ui.button>
            </div>
            @if ($messages->isEmpty())
                <x-ui.empty-state title="Belum ada ucapan" icon="heart">Ucapan tamu muncul di sini. Kamu bisa menyembunyikan yang tidak pantas dari undangan.</x-ui.empty-state>
            @else
                <ul class="divide-y divide-ink/10 overflow-hidden rounded-2xl border border-ink/10 bg-white" role="list">
                    @foreach ($messages as $message)
                        <li class="grid gap-3 p-5 sm:grid-cols-[1fr_auto] sm:items-start">
                            <div class="grid gap-1">
                                <p class="flex flex-wrap items-center gap-2 font-semibold text-ink">
                                    {{ $message->name }}
                                    @unless ($message->is_visible)<x-ui.badge>Disembunyikan</x-ui.badge>@endunless
                                </p>
                                <p class="whitespace-pre-line text-ink [overflow-wrap:anywhere]">{{ $message->message }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route($message->is_visible ? 'dashboard.responses.hide' : 'dashboard.responses.show', [$invitation, $message]) }}">
                                    @csrf
                                    <x-ui.button variant="secondary" type="submit">{{ $message->is_visible ? 'Sembunyikan' : 'Tampilkan' }}<span class="sr-only"> ucapan {{ $message->name }}</span></x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('dashboard.responses.destroy', [$invitation, $message]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" type="submit">Hapus<span class="sr-only"> ucapan {{ $message->name }}</span></x-ui.button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <x-ui.pagination :paginator="$messages" />
            @endif
        </section>
    </div>
</x-layouts.app>
