@props(['name', 'layer' => 'divider', 'corner' => null])
{{--
    Decorative ornament (TEMPLATE_SPEC "Slot ornamen"): an inline SVG drawn in currentColor, so one file takes the colour
    of every theme through --t-orn. `layer` fixes the stacking (z-index in invitation-core.css). Always aria-hidden and
    pointer-events: none, so it never covers text or a button. Only files from resources/svg/ornaments are read.
--}}
@php
    $layers = ['bg', 'pattern', 'frame', 'corner', 'divider', 'float'];
    $corners = ['tl', 'tr', 'bl', 'br'];
    $path = preg_match('/^[a-z0-9-]+$/', $name) ? resource_path("svg/ornaments/{$name}.svg") : null;
    $svg = $path !== null && is_file($path) ? file_get_contents($path) : '';
    $layer = in_array($layer, $layers, true) ? $layer : 'divider';
@endphp
<span {{ $attributes->class(['t-orn', "t-orn--{$layer}"]) }} aria-hidden="true" data-orn="{{ $name }}" @if ($layer === 'corner' && in_array($corner, $corners, true)) data-corner="{{ $corner }}" @endif>{!! $svg !!}</span>
