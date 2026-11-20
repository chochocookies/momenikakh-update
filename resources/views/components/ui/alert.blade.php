@props(['tone' => 'info', 'title' => null])
{{--
    A message in the page flow. Success and info are polite (role="status"); warning and danger interrupt (role="alert").
    The icon never carries the meaning alone: the title or text always says it too.
--}}
@php
    $tones = [
        'info' => ['box' => 'bg-rose-light text-ink ring-ink/15', 'icon' => 'info', 'role' => 'status'],
        'success' => ['box' => 'bg-emerald-900/10 text-emerald-900 ring-emerald-900/30', 'icon' => 'circle-check', 'role' => 'status'],
        'warning' => ['box' => 'bg-gold/15 text-ink ring-gold', 'icon' => 'circle-alert', 'role' => 'alert'],
        'danger' => ['box' => 'bg-danger/10 text-danger ring-danger/40', 'icon' => 'circle-alert', 'role' => 'alert'],
    ];
    $t = $tones[$tone] ?? $tones['info'];
@endphp
<div {{ $attributes->class(['flex items-start gap-3 rounded-xl p-4 text-base ring-1 ring-inset', $t['box']]) }} role="{{ $t['role'] }}">
    <x-ui.icon :name="$t['icon']" class="mt-0.5" />
    <div class="grid gap-1">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div>{{ $slot }}</div>
    </div>
</div>
