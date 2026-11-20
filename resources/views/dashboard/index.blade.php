<x-layouts.app title="Undangan saya">
    @if (session('toast'))
        <div hidden data-toast="{{ session('toast') }}" data-tone="success"></div>
    @endif

    <div class="grid gap-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="grid gap-1">
                <h1 class="font-serif text-4xl font-semibold text-ink">Undangan saya</h1>
                <p class="text-muted">Halo, {{ $user->name }}.</p>
            </div>
            <x-ui.button :href="route('dashboard.invitations.create')">Buat undangan</x-ui.button>
        </header>

        @if ($onboarding)
            <section aria-labelledby="onboarding-title" class="grid gap-4 rounded-2xl border border-ink/10 bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="grid gap-1">
                        <h2 id="onboarding-title" class="font-serif text-2xl font-semibold text-ink">Mulai dari sini</h2>
                        <p class="text-muted">Empat langkah sampai undanganmu bisa dibagikan. Kamu bisa berhenti kapan saja dan melanjutkannya nanti.</p>
                    </div>
                    <form method="POST" action="{{ route('dashboard.onboarding.dismiss') }}">
                        @csrf
                        <x-ui.button variant="ghost" type="submit">Tutup daftar ini</x-ui.button>
                    </form>
                </div>
                <ol class="grid gap-1">
                    @foreach ($onboarding as $i => $step)
                        <li>
                            <a href="{{ route($step['route'], $step['params']) }}" class="inline-flex min-h-11 items-center gap-3 rounded-xl px-2 text-ink hover:bg-rose-light">
                                <x-ui.icon :name="$step['done'] ? 'circle-check' : 'arrow-right'" :class="$step['done'] ? 'text-emerald-900' : 'text-rose'" />
                                <span @class(['text-muted line-through' => $step['done']])>{{ $i + 1 }}. {{ $step['label'] }}</span>
                                @if ($step['done'])<span class="sr-only">(selesai)</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($invitations->isEmpty())
            @unless ($onboarding)
            <x-ui.empty-state title="Belum ada undangan" description="Mulai dengan undangan sample gratis: isi datanya dan coba semua template. Bayar hanya saat kamu siap menerbitkannya." icon="heart">
                <x-ui.button :href="route('dashboard.invitations.create')">Buat undangan</x-ui.button>
            </x-ui.empty-state>
            @endunless
        @else
            <ul class="divide-y divide-ink/10 overflow-hidden rounded-2xl border border-ink/10 bg-white" role="list" aria-label="Daftar undangan">
                @foreach ($invitations as $invitation)
                    <li class="grid gap-3 p-5 sm:grid-cols-[1fr_auto] sm:items-center">
                        <div class="grid gap-1">
                            <h2 class="font-serif text-2xl font-semibold text-ink">
                                <a href="{{ route('dashboard.invitations.show', $invitation) }}" class="hover:text-rose">{{ $invitation->title }}</a>
                            </h2>
                            <p class="text-sm text-muted">
                                {{ $eventLabels[$invitation->event_type->value] ?? $invitation->event_type->value }}
                                · {{ $templateNames[$invitation->template_slug] ?? 'Template belum dipilih' }}
                            </p>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <x-ui.badge :tone="match ($invitation->status->value) { 'published' => 'success', 'expired' => 'danger', default => 'neutral' }">{{ $invitation->status->label() }}</x-ui.badge>
                                @if ($paid[$invitation->id] ?? false)
                                    <x-ui.badge tone="rose">Paket Template</x-ui.badge>
                                @else
                                    <x-ui.badge>Sample gratis</x-ui.badge>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button :href="route('dashboard.invitations.edit', $invitation)">Isi data</x-ui.button>
                            <x-ui.button variant="secondary" :href="route('dashboard.invitations.show', $invitation)">Buka</x-ui.button>
                            <x-ui.button variant="secondary" :href="route('dashboard.guests.index', $invitation)">Tamu</x-ui.button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <x-ui.pagination :paginator="$invitations" />
        @endif
    </div>
</x-layouts.app>
