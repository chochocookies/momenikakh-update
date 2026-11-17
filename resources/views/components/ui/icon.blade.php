@props(['name', 'size' => 'md', 'label' => null])
@php
    $sizes = ['sm' => 'size-4', 'md' => 'size-5', 'lg' => 'size-6', 'xl' => 'size-10'];
    $svg = preg_match('/^[a-z0-9-]+$/', $name) && is_file($p = resource_path("svg/icons/{$name}.svg"))
        ? file_get_contents($p) : '';
@endphp
<span {{ $attributes->class(['inline-flex shrink-0 [&>svg]:size-full', $sizes[$size]]) }}
      @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>{!! $svg !!}</span>
