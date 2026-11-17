@props(['name', 'label', 'hint' => null, 'required' => false])
@php $id = 'f-'.str_replace(['[', ']', '.'], '-', $name); $error = $errors->first($name); @endphp
<div {{ $attributes->class('grid gap-1.5') }}>
    <label for="{{ $id }}" class="text-sm font-semibold text-ink">{{ $label }}
        @if ($required)<span aria-hidden="true" class="text-danger">*</span><span class="sr-only">(wajib)</span>@endif
    </label>
    {{ $slot }}
    @if ($hint)<p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>@endif
    @if ($error)<p id="{{ $id }}-error" role="alert" class="text-sm font-semibold text-danger">{{ $error }}</p>@endif
</div>
