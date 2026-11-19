<x-layouts.public :title="'Pratinjau '.$manifest->name" description="Pratinjau template undangan dengan data contoh.">
    <div class="mx-auto flex min-h-svh max-w-6xl flex-col gap-4 px-4 py-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-serif text-3xl font-semibold text-ink">{{ $manifest->name }}</h1>
                <p class="text-sm text-muted">Pratinjau dengan data contoh. Semua nama di sini fiktif.</p>
            </div>

            <nav aria-label="Pengaturan pratinjau" class="flex flex-wrap items-center gap-2">
                <ul class="flex flex-wrap gap-2" role="list" aria-label="Jenis acara">
                    @foreach ($eventTypes as $type)
                        <li>
                            <a href="{{ route('public.preview', ['template' => $manifest->slug, 'event' => $type->value, 'mode' => $mode]) }}"
                                @if ($type === $event) aria-current="page" @endif
                                class="inline-flex min-h-11 items-center rounded-full px-4 text-base font-semibold ring-1 ring-inset ring-ink aria-[current=page]:bg-ink aria-[current=page]:text-white">{{ config("event-types.{$type->value}.label") }}</a>
                        </li>
                    @endforeach
                </ul>
                <ul class="flex gap-2" role="list" aria-label="Ukuran layar">
                    @foreach ($modes as $option)
                        <li>
                            <a href="{{ route('public.preview', ['template' => $manifest->slug, 'event' => $event->value, 'mode' => $option]) }}"
                                @if ($option === $mode) aria-current="page" @endif
                                class="inline-flex min-h-11 items-center rounded-full px-4 text-base font-semibold ring-1 ring-inset ring-ink aria-[current=page]:bg-ink aria-[current=page]:text-white">{{ $option === 'phone' ? 'Ponsel' : 'Desktop' }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </header>

        <div class="flex flex-1 justify-center rounded-xl bg-white p-3 ring-1 ring-ink/10">
            <iframe src="{{ $frameUrl }}" title="Pratinjau {{ $manifest->name }}" loading="lazy"
                class="{{ $mode === 'phone' ? 'h-[780px] w-[390px] max-w-full' : 'h-[780px] w-full' }} rounded-lg bg-ivory ring-1 ring-ink/20"></iframe>
        </div>
    </div>
</x-layouts.public>
