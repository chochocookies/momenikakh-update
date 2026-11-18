@props(['label' => 'Tab navigasi', 'tabs' => []])
<div {{ $attributes }}>
    <div role="tablist" aria-label="{{ $label }}" class="flex gap-2 border-b border-ink/10">
        @foreach ($tabs as $index => $tab)
            <button type="button"
                role="tab"
                id="tab-{{ $tab['id'] }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                aria-controls="panel-{{ $tab['id'] }}"
                tabindex="{{ $index === 0 ? '0' : '-1' }}"
                class="min-h-11 border-b-2 border-transparent px-4 py-2 text-sm font-medium aria-selected:border-rose aria-selected:text-rose">
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>
    {{ $slot }}
</div>
