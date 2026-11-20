@php
    use App\Domain\Billing\Money;

    $problems = array_filter($readiness['steps']);
    $stepNames = ['basics' => 'Dasar', 'people' => 'Orang', 'events' => 'Acara dan lokasi', 'story' => 'Cerita', 'gifts' => 'Amplop digital'];
    $guestLine = $guestCount === 0 ? 'Belum ada tamu. Tambahkan satu per satu atau impor dari CSV.' : "{$guestCount} tamu, masing-masing punya tautan sendiri.";
@endphp
<x-layouts.app :title="$invitation->title">
    @if (session('toast'))
        <div hidden data-toast="{{ session('toast') }}" data-tone="success"></div>
    @endif

    <div class="grid gap-8">
        <header class="grid gap-3">
            <p><a class="inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('dashboard') }}">Semua undangan</a></p>
            <h1 class="font-serif text-4xl font-semibold text-ink">{{ $invitation->title }}</h1>
            <p class="text-muted">{{ $eventLabel }} · {{ $template?->name ?? 'Template belum dipilih' }}</p>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge :tone="match ($invitation->status->value) { 'published' => 'success', 'expired' => 'danger', default => 'neutral' }">{{ $invitation->status->label() }}</x-ui.badge>
                @if ($canPublish)
                    <x-ui.badge tone="rose">Paket Template</x-ui.badge>
                @else
                    <x-ui.badge>Sample gratis</x-ui.badge>
                @endif
                <form method="POST" action="{{ route('dashboard.invitations.duplicate', $invitation) }}" class="ml-auto">
                    @csrf
                    <x-ui.button variant="ghost" type="submit"><x-ui.icon name="copy" size="sm" />Duplikat undangan</x-ui.button>
                </form>
            </div>
        </header>

        {{-- A real sequence: the order is the point, so the steps are numbered. --}}
        <ol class="divide-y divide-ink/10 overflow-hidden rounded-2xl border border-ink/10 bg-white" aria-label="Langkah menuju undangan terbit">
            <li class="grid gap-3 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-start sm:gap-5" id="langkah-data">
                <x-ui.icon :name="$readiness['ready'] ? 'circle-check' : 'pencil'" size="lg" :class="$readiness['ready'] ? 'text-emerald-900' : 'text-rose'" />
                <div class="grid gap-1">
                    <h2 class="text-lg font-semibold text-ink">1. Isi data</h2>
                    @if ($readiness['ready'])
                        <p class="text-muted">Datanya lengkap. Kamu masih bisa mengubahnya kapan saja.</p>
                    @else
                        <p class="text-muted">Lengkapi bagian ini sebelum menerbitkan:</p>
                        <ul class="list-disc pl-5 text-muted" role="list">
                            @foreach ($problems as $step => $message)
                                <li><span class="font-semibold text-ink">{{ $stepNames[$step] ?? $step }}:</span> {{ $message }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div><x-ui.button :variant="$readiness['ready'] ? 'secondary' : 'primary'" :href="route('dashboard.invitations.edit', $invitation)">Isi data</x-ui.button></div>
            </li>

            <li class="grid gap-3 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-start sm:gap-5" id="langkah-pratinjau">
                <x-ui.icon name="eye" size="lg" class="text-rose" />
                <div class="grid gap-1">
                    <h2 class="text-lg font-semibold text-ink">2. Lihat pratinjau</h2>
                    <p class="text-muted">Lihat templatenya di ponsel dan layar lebar. Pratinjau memakai data contoh, bukan datamu.</p>
                </div>
                <div>
                    @if ($template)
                        <x-ui.button variant="secondary" :href="route('public.preview', ['template' => $template->slug, 'event' => $invitation->event_type->value])" target="_blank" rel="noopener noreferrer">Lihat pratinjau<span class="sr-only"> (tab baru)</span></x-ui.button>
                    @endif
                </div>
            </li>

            <li class="grid gap-3 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-start sm:gap-5" id="langkah-tamu">
                <x-ui.icon :name="$guestCount > 0 ? 'circle-check' : 'users'" size="lg" :class="$guestCount > 0 ? 'text-emerald-900' : 'text-rose'" />
                <div class="grid gap-1">
                    <h2 class="text-lg font-semibold text-ink">3. Siapkan tamu</h2>
                    <p class="text-muted">{{ $guestLine }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-ui.button variant="secondary" :href="route('dashboard.guests.index', $invitation)">Kelola tamu</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('dashboard.responses.index', $invitation)">Jawaban tamu</x-ui.button>
                </div>
            </li>

            <li class="grid gap-3 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-start sm:gap-5" id="langkah-terbit" aria-labelledby="publish-title">
                <x-ui.icon :name="$publicUrl ? 'circle-check' : 'share-2'" size="lg" :class="$publicUrl ? 'text-emerald-900' : 'text-rose'" />
                <div class="grid gap-2">
                    <h2 id="publish-title" class="text-lg font-semibold text-ink">4. Terbitkan dan bagikan</h2>
                    @if ($publicUrl)
                        <p class="text-muted">Undanganmu sudah terbit. Bagikan tautan ini.</p>
                        <p class="break-all font-semibold text-ink">{{ $publicUrl }}</p>
                    @elseif (! $canPublish)
                        <p class="text-muted">Undangan ini masih sample gratis: bisa diisi dan dipratinjau, belum bisa diterbitkan.</p>
                        <p class="text-ink"><span class="tabular font-serif text-3xl font-semibold text-rose">{{ Money::rupiah($plans['paid']['price']) }}</span> untuk {{ $plans['paid']['name'] }}, sekali bayar per undangan, tanpa masa kedaluwarsa.</p>
                        @unless (config('momenikah.features.payments'))
                            <p class="text-sm text-muted">Pembayaran online segera dibuka.</p>
                        @endunless
                    @elseif (! $readiness['ready'])
                        <p class="text-muted">Paket Template sudah aktif. Lengkapi datanya dulu, lalu terbitkan.</p>
                    @else
                        <p class="text-muted">Paket Template sudah aktif dan datanya lengkap. Tautannya terbuka untuk siapa pun yang memilikinya.</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($publicUrl)
                        <x-ui.button variant="secondary" type="button" data-copy="{{ $publicUrl }}" data-copy-done="Tautan undangan disalin.">Salin tautan</x-ui.button>
                        <x-ui.button :href="$shareUrl" target="_blank" rel="noopener noreferrer">Bagikan lewat WhatsApp<span class="sr-only"> (tab baru)</span></x-ui.button>
                        <form method="POST" action="{{ route('dashboard.invitations.unpublish', $invitation) }}">
                            @csrf
                            <x-ui.button variant="secondary" type="submit">Jadikan draf</x-ui.button>
                        </form>
                    @elseif ($canPublish && $readiness['ready'])
                        <form method="POST" action="{{ route('dashboard.invitations.publish', $invitation) }}">
                            @csrf
                            <x-ui.button type="submit">Terbitkan</x-ui.button>
                        </form>
                    @endif
                </div>
            </li>
        </ol>

        @if ($stats)
            <section aria-labelledby="stats-title" class="grid gap-3">
                <h2 id="stats-title" class="font-serif text-2xl font-semibold text-ink">Statistik</h2>
                <div class="relative overflow-x-auto rounded-2xl border border-ink/10 bg-white" tabindex="0" role="region" aria-label="Statistik undangan">
                    <table class="w-full text-left">
                        <caption class="sr-only">Ringkasan {{ $stats['days'] }} hari terakhir</caption>
                        <thead class="bg-rose-light text-ink">
                            <tr><th scope="col" class="px-4 py-3">{{ $stats['days'] }} hari terakhir</th><th scope="col" class="px-4 py-3 text-right">Jumlah</th></tr>
                        </thead>
                        <tbody class="divide-y divide-ink/10">
                            @foreach (['Pengunjung unik' => $stats['unique_visitors'], 'Kunjungan' => $stats['views'], 'Undangan dibuka' => $stats['opens'], 'Konfirmasi kehadiran' => $stats['rsvps'], 'Tamu yang akan hadir' => $stats['attending_people'], 'Ucapan tampil' => $stats['wishes']] as $label => $value)
                                <tr><th scope="row" class="px-4 py-3 font-medium text-ink">{{ $label }}</th><td class="px-4 py-3 text-right font-semibold text-ink">{{ $value }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p><a class="inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('dashboard.responses.index', $invitation) }}">Lihat jawaban tamu</a></p>
            </section>
        @endif
    </div>
</x-layouts.app>
