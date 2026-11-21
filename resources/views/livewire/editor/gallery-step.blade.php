{{-- DSH-12: gallery upload with drag-and-drop, progress, reorder, and size limits. --}}
@php
    $maxMb = max(1, intdiv($imageMaxBytes, 1024 * 1024));
    $count = count($galleryItems);
    $limitLabel = $maxPhotos === null ? null : "{$count} dari {$maxPhotos} foto";
@endphp
<div class="grid gap-5" data-editor-media="gallery" data-max-bytes="{{ $imageMaxBytes }}">
    <p class="text-sm text-muted">
        Seret foto ke kotak di bawah atau pilih dari perangkat.
        JPEG, PNG, atau WebP, maksimal {{ $maxMb }} MB per berkas.
        @if ($limitLabel)
            <span class="font-semibold text-ink">{{ $limitLabel }}.</span>
        @endif
    </p>

    @php
        $galleryError = collect($errors->getBags())->flatMap->getMessages()
            ->first(fn (array $messages, string $key): bool => str_starts_with($key, 'galleryPhotos'));
        $galleryError = is_array($galleryError) ? ($galleryError[0] ?? null) : null;
    @endphp
    @if ($galleryError)
        <x-ui.alert tone="danger">{{ $galleryError }}</x-ui.alert>
    @endif

    <div
        data-dropzone
        class="grid place-items-center gap-3 rounded-2xl border border-dashed border-ink/25 bg-rose-light/40 px-6 py-10 text-center transition motion-reduce:transition-none data-[active=true]:border-rose data-[active=true]:bg-rose-light"
    >
        <x-ui.icon name="image" size="lg" class="text-rose" />
        <div class="grid gap-1">
            <p class="font-semibold text-ink">Lepaskan foto di sini</p>
            <p class="text-sm text-muted">atau</p>
        </div>
        <label class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-full bg-rose px-6 text-base font-bold text-white hover:bg-rose/90 focus-within:outline-3 focus-within:outline-offset-3 focus-within:outline-rose">
            <span>Pilih foto</span>
            <input
                type="file"
                class="sr-only"
                accept="image/jpeg,image/png,image/webp"
                multiple
                wire:model="galleryPhotos"
                data-upload-input
                @if ($maxPhotos !== null && $count >= $maxPhotos) disabled @endif
            >
        </label>
        <div data-upload-progress hidden class="w-full max-w-xs grid gap-2" role="status" aria-live="polite">
            <p class="text-sm font-semibold text-ink">Mengunggah… <span data-upload-percent>0</span>%</p>
            <div class="h-2 overflow-hidden rounded-full bg-ink/10" aria-hidden="true">
                <div data-upload-bar class="h-full w-0 rounded-full bg-rose transition-[width] motion-reduce:transition-none"></div>
            </div>
        </div>
    </div>

    @if ($galleryItems === [])
        <x-ui.empty-state title="Belum ada foto" description="Tambahkan foto kenangan untuk ditampilkan di galeri undangan." icon="image" />
    @else
        <ul class="grid gap-3 sm:grid-cols-2" role="list" data-gallery-list>
            @foreach ($galleryItems as $index => $item)
                <li
                    wire:key="gallery-{{ $item['id'] }}"
                    data-gallery-id="{{ $item['id'] }}"
                    draggable="true"
                    class="group grid grid-cols-[4.5rem_minmax(0,1fr)_auto] items-center gap-3 rounded-xl border border-ink/10 bg-white p-3"
                >
                    <img src="{{ $item['url'] }}" alt="{{ $item['alt'] !== '' ? $item['alt'] : 'Foto galeri '.($index + 1) }}" width="72" height="72" class="size-[4.5rem] rounded-lg object-cover" loading="lazy" decoding="async">
                    <div class="min-w-0 grid gap-1">
                        <p class="truncate text-sm font-semibold text-ink">Foto {{ $index + 1 }}</p>
                        <div class="flex flex-wrap gap-1">
                            <button type="button" wire:click="moveGalleryItem({{ $item['id'] }}, 'up')" @if ($index === 0) disabled @endif
                                class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-bold text-rose hover:bg-rose-light disabled:pointer-events-none disabled:opacity-50"
                                aria-label="Naikkan foto {{ $index + 1 }}">Naik</button>
                            <button type="button" wire:click="moveGalleryItem({{ $item['id'] }}, 'down')" @if ($index === count($galleryItems) - 1) disabled @endif
                                class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-bold text-rose hover:bg-rose-light disabled:pointer-events-none disabled:opacity-50"
                                aria-label="Turunkan foto {{ $index + 1 }}">Turun</button>
                        </div>
                    </div>
                    <button type="button" wire:click="removeGalleryItem({{ $item['id'] }})" wire:confirm="Hapus foto ini dari galeri?"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full text-rose hover:bg-rose-light"
                        aria-label="Hapus foto {{ $index + 1 }}">
                        <x-ui.icon name="trash-2" size="sm" />
                    </button>
                </li>
            @endforeach
        </ul>
        <p class="text-sm text-muted">Seret kartu untuk mengubah urutan, atau pakai tombol Naik dan Turun.</p>
    @endif
</div>
