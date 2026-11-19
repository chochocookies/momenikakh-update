@props(['type' => 'text'])
@aware(['name', 'hint' => null, 'required' => false])
@php
    $id = 'f-'.str_replace(['[', ']', '.'], '-', $name);
    $invalid = $errors->has($name);
    $described = collect([$hint ? "$id-hint" : null, $invalid ? "$id-error" : null])->filter()->implode(' ');
@endphp
<input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @required($required)
    @if ($invalid) aria-invalid="true" @endif @if ($described) aria-describedby="{{ $described }}" @endif
    {{ $attributes->class('min-h-11 w-full rounded-xl bg-white px-4 text-base text-ink ring-1 ring-inset ring-muted
        placeholder:text-muted focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-rose
        aria-invalid:ring-2 aria-invalid:ring-danger') }}>
