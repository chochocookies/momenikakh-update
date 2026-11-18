@props(['id' => null, 'title'])
@php $id = $id ?? 'modal-'.uniqid(); @endphp
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title"
    {{ $attributes->class(['w-full max-w-lg rounded-2xl border border-ink/10 bg-ivory p-6 text-ink shadow-xl backdrop:bg-ink/60']) }}>
    <div class="flex items-start justify-between gap-4">
        <h2 id="{{ $id }}-title" class="font-serif text-2xl font-semibold">{{ $title }}</h2>
        <button type="button" data-modal-close aria-label="Tutup dialog"
            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl text-muted hover:bg-ink/5 hover:text-ink">
            <x-ui.icon name="x" size="md" />
        </button>
    </div>
    <div class="mt-4">
        {{ $slot }}
    </div>
</dialog>
