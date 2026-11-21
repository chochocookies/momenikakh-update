@php use Illuminate\Support\Str; @endphp
<div class="grid gap-6 [&>*]:min-w-0">
    <header class="grid gap-1">
        <p><a class="inline-flex min-h-11 items-center text-rose underline underline-offset-4" href="{{ route('dashboard.invitations.show', $invitation) }}">Kembali ke undangan</a></p>
        <h1 class="font-serif text-4xl font-semibold text-ink">Isi data undangan</h1>
        <p class="text-muted">{{ $invitation->title }} · {{ $definition->label }}</p>
    </header>

    <div class="grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)] lg:items-start xl:grid-cols-[15rem_minmax(0,1fr)_22rem]">
        <div class="grid gap-4 lg:sticky lg:top-6">
            <nav aria-label="Langkah pengisian">
                <ol class="flex gap-2 overflow-x-auto pb-1 lg:grid lg:gap-1 lg:overflow-visible lg:pb-0">
                    @foreach ($steps as $key => $label)
                        <li class="shrink-0">
                            <button type="button" wire:click="selectStep('{{ $key }}')" @if ($key === $step) aria-current="step" @endif
                                class="inline-flex min-h-11 w-full items-center justify-between gap-3 rounded-full px-4 text-sm font-semibold text-ink ring-1 ring-inset ring-ink/15 transition hover:bg-rose-light motion-reduce:transition-none lg:rounded-xl lg:ring-0 aria-[current=step]:bg-rose aria-[current=step]:text-white aria-[current=step]:ring-rose">
                                <span>{{ $label }}</span>
                                @if (in_array($key, $unsaved, true))
                                    <span aria-hidden="true">•</span><span class="sr-only">(belum tersimpan)</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ol>
            </nav>
            <p class="xl:hidden"><a class="inline-flex min-h-11 items-center gap-2 text-sm text-rose underline underline-offset-4" target="_blank" rel="noopener noreferrer" href="{{ route('dashboard.invitations.preview', $invitation) }}"><x-ui.icon name="eye" size="sm" />Lihat undanganmu<span class="sr-only"> (tab baru)</span></a></p>
        </div>

        <section aria-labelledby="step-title" class="grid gap-5 rounded-2xl border border-ink/10 bg-white p-6">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                <h2 id="step-title" class="font-serif text-2xl font-semibold text-ink">{{ $steps[$step] }}</h2>
                <p role="status" aria-live="polite" @class(['text-sm', 'text-muted' => ! in_array($step, $unsaved, true), 'font-semibold text-danger' => in_array($step, $unsaved, true)])>
                    @if (in_array($step, $unsaved, true))
                        Ada isian yang belum tersimpan. Periksa kolom yang diberi tanda.
                    @elseif ($savedAt)
                        Tersimpan otomatis pukul {{ $savedAt }}.
                    @else
                        Perubahan tersimpan otomatis saat kamu pindah ke kolom lain.
                    @endif
                </p>
            </div>

            @if ($isMedia && $step === 'gallery')
                @include('livewire.editor.gallery-step')
            @elseif ($isMedia && $step === 'music')
                @include('livewire.editor.music-step')
            @elseif ($isList)
                @if ($errors->has('form.'.$step))
                    <x-ui.alert tone="danger">{{ $errors->first('form.'.$step) }}</x-ui.alert>
                @endif

                <div class="divide-y divide-ink/10">
                    @forelse ($form[$step] as $i => $row)
                        <fieldset class="grid gap-4 py-5 first:pt-0" wire:key="{{ $step }}-{{ $row['id'] ?? 'new' }}-{{ $i }}">
                            <legend class="float-left mb-4 w-full font-semibold text-ink">{{ $steps[$step] }} {{ $i + 1 }}</legend>
                            <div class="grid clear-both gap-4 sm:grid-cols-2">
                                @foreach ($fields as $field)
                                    @include('livewire.editor.field', ['path' => "form.{$step}.{$i}.{$field['key']}", 'field' => $field, 'current' => $row[$field['key']] ?? ''])
                                @endforeach
                            </div>
                            <div>
                                <x-ui.button variant="ghost" type="button" wire:click="removeRow('{{ $step }}', {{ $i }})">
                                    <x-ui.icon name="trash-2" size="sm" />Hapus {{ Str::lower($steps[$step]) }} {{ $i + 1 }}
                                </x-ui.button>
                            </div>
                        </fieldset>
                    @empty
                        <p class="py-2 text-muted">Belum ada isian. Tambahkan yang pertama.</p>
                    @endforelse
                </div>

                @if (count($form[$step]) < $maxRows)
                    <div><x-ui.button variant="secondary" type="button" wire:click="addRow('{{ $step }}')"><x-ui.icon name="plus" size="sm" />Tambah {{ Str::lower($steps[$step]) }}</x-ui.button></div>
                @endif
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($fields as $field)
                        @php
                            $path = in_array($step, ['content', 'settings'], true) ? "form.{$step}.{$field['key']}" : "form.{$field['key']}";
                        @endphp
                        <div @class(['sm:col-span-2' => $field['type'] === 'textarea'])>
                            @include('livewire.editor.field', ['path' => $path, 'field' => $field, 'current' => data_get($form, Str::after($path, 'form.'))])
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- DSH-04: the draft as guests will see it. wire:ignore keeps Livewire from touching the frame; editor-preview.js reloads it after each save. --}}
        <aside class="hidden xl:sticky xl:top-6 xl:block" aria-label="Pratinjau undanganmu" wire:ignore>
            <iframe id="draft-preview" src="{{ route('dashboard.invitations.preview', $invitation) }}" title="Pratinjau undanganmu" loading="lazy" class="h-[40rem] w-full rounded-2xl border border-ink/10 bg-white"></iframe>
            <p class="mt-2 text-sm text-muted">Pratinjau memuat ulang setelah setiap perubahan tersimpan.</p>
        </aside>
    </div>
</div>
