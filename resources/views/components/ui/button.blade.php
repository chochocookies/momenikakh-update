@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button', 'loading' => false])
@php
    $base = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full font-bold transition motion-reduce:transition-none
             focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-rose
             disabled:pointer-events-none disabled:opacity-50';
    $variants = [
        'primary'   => 'bg-rose text-white hover:bg-rose/90',
        'secondary' => 'text-ink ring-1 ring-inset ring-ink hover:bg-rose-light',
        'ghost'     => 'text-rose hover:bg-rose-light',
        'danger'    => 'bg-danger text-white hover:bg-danger/90',
    ];
    $sizes = ['md' => 'px-6 text-base', 'lg' => 'min-h-12 px-8 text-lg'];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$base, $variants[$variant], $sizes[$size]]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($loading) @if ($loading) aria-busy="true" @endif
            {{ $attributes->class([$base, $variants[$variant], $sizes[$size]]) }}>{{ $slot }}</button>
@endif
