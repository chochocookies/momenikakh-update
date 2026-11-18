@props(['title', 'description' => null, 'icon' => 'sparkles'])
<div {{ $attributes->class(['flex flex-col items-center justify-center rounded-2xl border border-dashed border-ink/20 bg-white/60 px-6 py-12 text-center']) }}>
    <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-rose/10 text-rose">
        <x-ui.icon :name="$icon" size="lg" />
    </div>
    <h3 class="font-serif text-xl font-semibold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-md text-sm text-muted">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
