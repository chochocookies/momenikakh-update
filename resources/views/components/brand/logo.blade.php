@props(['tag' => 'span'])
{{-- The two interlocking rings and the heart, with the wordmark. Rings and text follow currentColor. --}}
<{{ $tag }} {{ $attributes->class(['lp-logo']) }}>
    <svg viewBox="0 0 40 38" aria-hidden="true" focusable="false"><circle class="lp-ring-a" cx="15" cy="24" r="10"/><circle class="lp-ring-b" cx="25" cy="24" r="10"/><path class="lp-heart" d="M20 12.5S14 9.5 14 6c0-2.8 3.6-3.3 6-.4 2.4-2.9 6-2.4 6 .4 0 3.5-6 6.5-6 6.5z"/></svg>
    Momenikah
</{{ $tag }}>
