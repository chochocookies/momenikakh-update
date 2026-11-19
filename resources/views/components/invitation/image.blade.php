@props(['image', 'sizes' => '100vw', 'lazy' => true])
{{-- One MediaData picture with the attributes that prevent layout shift (width and height) and an alt text. --}}
@php
    [$src, $srcset, $w, $h, $alt] = [$image->url, $image->srcset, $image->width, $image->height, $image->alt];
@endphp
<img src="{{ $src }}"
    @if ($srcset !== '') srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
    width="{{ $w }}" height="{{ $h }}" alt="{{ $alt }}"
    @if ($lazy) loading="lazy" @else fetchpriority="high" @endif decoding="async"
    {{ $attributes }}>
