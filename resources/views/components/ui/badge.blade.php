@props(['tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'bg-ink/5 text-ink',
        'rose' => 'bg-rose/10 text-rose',
        'success' => 'bg-emerald-900/10 text-emerald-900',
        'danger' => 'bg-danger/10 text-danger',
    ];
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium', $tones[$tone] ?? $tones['neutral']]) }}>
    {{ $slot }}
</span>
