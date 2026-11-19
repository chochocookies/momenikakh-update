<x-layouts.app title="Undangan saya">
    @if (session('toast'))
        <div hidden data-toast="{{ session('toast') }}" data-tone="success"></div>
    @endif

    <div class="grid gap-8">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="grid gap-1">
                <h1 class="font-serif text-4xl font-semibold text-ink">Undangan saya</h1>
                <p class="text-muted">Halo, {{ $user->name }}.</p>
            </div>
            <x-ui.button :href="route('dashboard.invitations.create')">Buat undangan</x-ui.button>
        </header>

        @if ($invitations->isEmpty())
            <x-ui.empty-state title="Belum ada undangan" description="Mulai dengan undangan sample gratis: isi datanya dan coba semua template. Bayar hanya saat kamu siap menerbitkannya." icon="heart">
                <x-ui.button :href="route('dashboard.invitations.create')">Buat undangan</x-ui.button>
            </x-ui.empty-state>
        @else
            <ul class="grid gap-4 md:grid-cols-2" role="list" aria-label="Daftar undangan">
                @foreach ($invitations as $invitation)
                    <li>
                        <x-ui.card class="grid h-full gap-4">
                            <div class="grid gap-2">
                                <h2 class="font-serif text-2xl font-semibold text-ink">
                                    <a href="{{ route('dashboard.invitations.show', $invitation) }}" class="hover:text-rose">{{ $invitation->title }}</a>
                                </h2>
                                <p class="text-sm text-muted">
                                    {{ $eventLabels[$invitation->event_type->value] ?? $invitation->event_type->value }}
                                    · {{ $templateNames[$invitation->template_slug] ?? 'Template belum dipilih' }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.badge :tone="match ($invitation->status->value) { 'published' => 'success', 'expired' => 'danger', default => 'neutral' }">{{ $invitation->status->label() }}</x-ui.badge>
                                    @if ($paid[$invitation->id] ?? false)
                                        <x-ui.badge tone="rose">Paket Template</x-ui.badge>
                                    @else
                                        <x-ui.badge>Sample gratis</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-auto flex flex-wrap gap-2">
                                <x-ui.button :href="route('dashboard.invitations.show', $invitation)">Buka</x-ui.button>
                                <x-ui.button variant="secondary" :href="route('dashboard.guests.index', $invitation)">Tamu</x-ui.button>
                            </div>
                        </x-ui.card>
                    </li>
                @endforeach
            </ul>

            {{ $invitations->links() }}
        @endif
    </div>
</x-layouts.app>
