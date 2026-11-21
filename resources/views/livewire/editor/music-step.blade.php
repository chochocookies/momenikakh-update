{{-- DSH-13: music upload and/or licensed catalog selection. --}}
@php
    $maxMb = max(1, intdiv($audioMaxBytes, 1024 * 1024));
    $source = $musicForm['source'] ?? null;
@endphp
<div class="grid gap-5" data-editor-media="music" data-max-bytes="{{ $audioMaxBytes }}">
    <p class="text-sm text-muted">
        Pilih musik latar untuk undangan. Tamu memutarnya setelah membuka sampul.
        @if ($canUploadMusic)
            Unggah MP3 atau OGG, maksimal {{ $maxMb }} MB.
        @endif
    </p>

    @if ($errors->has('musicFile') || $errors->has('musicForm.source') || $errors->has('musicForm.track_id') || $errors->has('musicForm.title'))
        <x-ui.alert tone="danger">
            {{ $errors->first('musicFile') ?: $errors->first('musicForm.source') ?: $errors->first('musicForm.track_id') ?: $errors->first('musicForm.title') }}
        </x-ui.alert>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div data-field="musicForm.source">
            <x-ui.select name="musicForm.source" id="f-music-source" label="Sumber musik" wire:model.live="musicForm.source">
                <option value="">Tanpa musik</option>
                @if ($canUploadMusic)
                    <option value="upload">Unggah sendiri</option>
                @endif
                @if ($catalogEnabled)
                    <option value="catalog">Katalog berlisensi</option>
                @endif
            </x-ui.select>
        </div>
        <div data-field="musicForm.title">
            <x-ui.field name="musicForm.title" label="Judul ditampilkan" hint="Opsional. Contoh: Lagu kami.">
                <x-ui.input type="text" autocomplete="off" wire:model.live.blur="musicForm.title" maxlength="80" />
            </x-ui.field>
        </div>
    </div>

    @if (! $canUploadMusic && ! $catalogEnabled)
        <x-ui.alert tone="info" title="Musik kustom belum tersedia">
            Paket undanganmu belum membuka unggah musik. Katalog berlisensi akan muncul di sini bila sudah diaktifkan.
        </x-ui.alert>
    @endif

    @if ($source === 'upload' && $canUploadMusic)
        <div
            data-dropzone
            class="grid place-items-center gap-3 rounded-2xl border border-dashed border-ink/25 bg-rose-light/40 px-6 py-10 text-center transition motion-reduce:transition-none data-[active=true]:border-rose data-[active=true]:bg-rose-light"
        >
            <x-ui.icon name="music" size="lg" class="text-rose" />
            <div class="grid gap-1">
                <p class="font-semibold text-ink">Lepaskan berkas audio di sini</p>
                <p class="text-sm text-muted">MP3 atau OGG · maksimal {{ $maxMb }} MB</p>
            </div>
            <label class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-full bg-rose px-6 text-base font-bold text-white hover:bg-rose/90 focus-within:outline-3 focus-within:outline-offset-3 focus-within:outline-rose">
                <span>Pilih berkas</span>
                <input type="file" class="sr-only" accept="audio/mpeg,audio/ogg,.mp3,.ogg" wire:model="musicFile" data-upload-input>
            </label>
            <div data-upload-progress hidden class="w-full max-w-xs grid gap-2" role="status" aria-live="polite">
                <p class="text-sm font-semibold text-ink">Mengunggah… <span data-upload-percent>0</span>%</p>
                <div class="h-2 overflow-hidden rounded-full bg-ink/10" aria-hidden="true">
                    <div data-upload-bar class="h-full w-0 rounded-full bg-rose transition-[width] motion-reduce:transition-none"></div>
                </div>
            </div>
        </div>

        @if ($musicItem['id'])
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink/10 bg-white p-4">
                <div class="min-w-0 grid gap-1">
                    <p class="font-semibold text-ink">{{ $musicItem['title'] ?? 'Musik latar' }}</p>
                    @if ($musicItem['url'])
                        <audio controls preload="none" class="max-w-full" src="{{ $musicItem['url'] }}">Browser tidak mendukung pemutar audio.</audio>
                    @endif
                </div>
                <x-ui.button variant="ghost" type="button" wire:click="removeMusicFile" wire:confirm="Hapus berkas musik ini?">
                    <x-ui.icon name="trash-2" size="sm" />Hapus berkas
                </x-ui.button>
            </div>
        @else
            <x-ui.empty-state title="Belum ada berkas" description="Unggah satu lagu untuk diputar di undangan." icon="music" />
        @endif
    @endif

    @if ($source === 'catalog' && $catalogEnabled)
        <div data-field="musicForm.track_id" class="grid gap-3">
            <p class="font-semibold text-ink">Pilih lagu katalog</p>
            @forelse ($catalogTracks as $track)
                <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border border-ink/10 bg-white p-4 has-[:checked]:border-rose has-[:checked]:ring-1 has-[:checked]:ring-rose">
                    <input type="radio" class="mt-1 size-5 accent-rose" wire:model.live="musicForm.track_id" name="music-track" value="{{ $track['id'] }}">
                    <span class="grid gap-0.5">
                        <span class="font-semibold text-ink">{{ $track['title'] }}</span>
                        @if ($track['artist'])
                            <span class="text-sm text-muted">{{ $track['artist'] }}</span>
                        @endif
                        @if ($track['attribution'])
                            <span class="text-sm text-muted">{{ $track['attribution'] }}</span>
                        @endif
                    </span>
                </label>
            @empty
                <x-ui.empty-state title="Katalog masih kosong" description="Belum ada lagu berlisensi yang bisa dipilih." icon="music" />
            @endforelse
        </div>
    @endif
</div>
