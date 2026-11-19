<x-layouts.app :title="$invitation->title">
    @if (session('toast'))
        <div hidden data-toast="{{ session('toast') }}" data-tone="success"></div>
    @endif

    <div class="grid gap-8">
        <header class="grid gap-3">
            <p><a class="inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('dashboard') }}">Semua undangan</a></p>
            <h1 class="font-serif text-4xl font-semibold text-ink">{{ $invitation->title }}</h1>
            <p class="text-muted">{{ $eventLabel }} · {{ $template?->name ?? 'Template belum dipilih' }}</p>
            <div class="flex flex-wrap gap-2">
                <x-ui.badge :tone="match ($invitation->status->value) { 'published' => 'success', 'expired' => 'danger', default => 'neutral' }">{{ $invitation->status->label() }}</x-ui.badge>
                @if ($canPublish)
                    <x-ui.badge tone="rose">Paket Template</x-ui.badge>
                @else
                    <x-ui.badge>Sample gratis</x-ui.badge>
                @endif
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card class="grid content-start gap-3">
                <h2 class="font-serif text-2xl font-semibold text-ink">Pratinjau dan tamu</h2>
                <p class="text-muted">Lihat templatenya dengan data contoh, lalu atur daftar tamu dan tautan WhatsApp mereka.</p>
                <div class="flex flex-wrap gap-3">
                    @if ($template)
                        <x-ui.button variant="secondary" :href="route('public.preview', ['template' => $template->slug, 'event' => $invitation->event_type->value])" target="_blank" rel="noopener noreferrer">Lihat pratinjau<span class="sr-only"> (tab baru)</span></x-ui.button>
                    @endif
                    <x-ui.button :href="route('dashboard.guests.index', $invitation)">Kelola tamu</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="grid content-start gap-3" aria-labelledby="publish-title">
                <h2 id="publish-title" class="font-serif text-2xl font-semibold text-ink">Penerbitan</h2>
                @if ($publicUrl)
                    <p class="text-muted">Undanganmu sudah terbit. Bagikan tautan ini.</p>
                    <p class="break-all font-semibold text-ink">{{ $publicUrl }}</p>
                    <div><x-ui.button variant="secondary" type="button" data-copy="{{ $publicUrl }}" data-copy-done="Tautan undangan disalin.">Salin tautan</x-ui.button></div>
                @elseif ($canPublish)
                    <p class="text-muted">Paket Template sudah aktif untuk undangan ini. Penerbitan dibuka setelah datanya lengkap.</p>
                @else
                    <p class="text-muted">Undangan ini masih sample gratis: bisa diisi dan dipratinjau, belum bisa diterbitkan.</p>
                    <p class="text-ink"><span class="font-serif text-3xl font-semibold text-rose">{{ \App\Domain\Billing\Money::rupiah($plans['paid']['price']) }}</span> untuk {{ $plans['paid']['name'] }}, sekali bayar per undangan, tanpa masa kedaluwarsa.</p>
                    <p class="text-sm text-muted">Pembayaran online segera dibuka.</p>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
