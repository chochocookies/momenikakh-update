<x-layouts.app title="Buat undangan">
    <div class="grid gap-8">
        @if ($definition === null)
            {{-- Step 1: the event type --}}
            <header class="grid gap-2">
                <p class="text-sm font-semibold text-rose">Langkah 1 dari 2</p>
                <h1 class="font-serif text-4xl font-semibold text-ink">Undangan untuk acara apa?</h1>
                <p class="max-w-2xl text-muted">Jenis acara menentukan isian dan template yang tersedia. Jenis acara tidak bisa diganti setelah undangan dibuat.</p>
            </header>

            <ul class="grid gap-4 md:grid-cols-2" role="list" aria-label="Jenis acara">
                @foreach ($available as $key => $type)
                    <li>
                        <x-ui.card class="grid h-full gap-3">
                            <h2 class="font-serif text-2xl font-semibold text-ink">{{ $type->label }}</h2>
                            <p class="text-muted">{{ $type->description }}</p>
                            <div class="mt-auto"><x-ui.button :href="route('dashboard.invitations.create', ['event' => $key])" aria-label="Pilih {{ $type->label }}">Pilih</x-ui.button></div>
                        </x-ui.card>
                    </li>
                @endforeach
            </ul>
            <p class="text-sm text-muted">Jenis acara lain menyusul.</p>
        @else
            {{-- Step 2: template, title and how to start --}}
            <header class="grid gap-2">
                <p class="text-sm font-semibold text-rose">Langkah 2 dari 2</p>
                <h1 class="font-serif text-4xl font-semibold text-ink">Pilih template untuk {{ mb_strtolower($definition->label) }}</h1>
                <p class="max-w-2xl text-muted">Kamu bisa membuka pratinjau setiap template dengan data contoh. Undangan baru berstatus sample gratis sampai kamu menerbitkannya dengan Paket Template.</p>
                <p><a class="inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('dashboard.invitations.create') }}">Ganti jenis acara</a></p>
            </header>

            <form method="POST" action="{{ route('dashboard.invitations.store') }}" class="grid gap-8">
                @csrf
                <input type="hidden" name="event_type" value="{{ $definition->key() }}">

                <fieldset class="grid gap-4">
                    <legend class="font-serif text-2xl font-semibold text-ink">Template</legend>
                    @error('template_slug')<p role="alert" class="text-base font-semibold text-danger">{{ $message }}</p>@enderror
                    <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" role="list">
                        @foreach ($templates as $slug => $manifest)
                            <li>
                                <label class="block h-full cursor-pointer">
                                    <input type="radio" name="template_slug" value="{{ $slug }}" class="peer sr-only" @checked(old('template_slug', $loop->first ? $slug : null) === $slug) required>
                                    <span class="grid h-full gap-3 rounded-2xl border border-ink/10 bg-white p-4 shadow-sm peer-checked:ring-2 peer-checked:ring-rose peer-focus-visible:outline-3 peer-focus-visible:outline-offset-3 peer-focus-visible:outline-rose">
                                        @if ($thumbs[$slug] ?? false)
                                            @php($thumbAlt = 'Tampilan '.$manifest->name)
                                            <img src="{{ $thumbs[$slug] }}" width="600" height="1200" alt="{{ $thumbAlt }}" loading="lazy" class="mx-auto h-64 w-auto rounded-xl object-cover ring-1 ring-ink/10">
                                        @endif
                                        <span class="font-serif text-2xl font-semibold text-ink">{{ $manifest->name }}</span>
                                        @if ($manifest->mood !== '')<span class="text-sm text-muted">{{ ucfirst($manifest->mood) }}</span>@endif
                                    </span>
                                </label>
                                <a class="mt-2 inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('public.preview', ['template' => $slug, 'event' => $definition->key()]) }}" target="_blank" rel="noopener noreferrer">Lihat pratinjau<span class="sr-only"> {{ $manifest->name }} (tab baru)</span></a>
                            </li>
                        @endforeach
                    </ul>
                </fieldset>

                <x-ui.field name="title" label="Judul undangan" hint="Contoh: Raka &amp; Sinta. Judul ini tampil di sampul dan dipakai untuk tautan undangan." :required="true">
                    <x-ui.input autocomplete="off" maxlength="120" :value="old('title')" />
                </x-ui.field>

                <fieldset class="grid gap-3">
                    <legend class="font-serif text-2xl font-semibold text-ink">Mulai dengan</legend>
                    @error('start')<p role="alert" class="text-base font-semibold text-danger">{{ $message }}</p>@enderror
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/10 bg-white p-4 has-[:checked]:ring-2 has-[:checked]:ring-rose">
                        <input type="radio" name="start" value="sample" class="mt-1 size-5 accent-rose" @checked(old('start', 'sample') === 'sample')>
                        <span class="grid gap-1"><span class="font-semibold text-ink">Data contoh (disarankan)</span><span class="text-muted">Semua bagian sudah terisi nama dan acara fiktif, jadi kamu langsung melihat bentuk akhirnya. Ganti dengan datamu kapan saja.</span></span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/10 bg-white p-4 has-[:checked]:ring-2 has-[:checked]:ring-rose">
                        <input type="radio" name="start" value="blank" class="mt-1 size-5 accent-rose" @checked(old('start') === 'blank')>
                        <span class="grid gap-1"><span class="font-semibold text-ink">Kosong</span><span class="text-muted">Mulai dari undangan tanpa isi.</span></span>
                    </label>
                </fieldset>

                <div><x-ui.button type="submit" size="lg">Buat undangan</x-ui.button></div>
            </form>
        @endif
    </div>
</x-layouts.app>
