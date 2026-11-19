@props(['name', 'label' => null, 'error' => null])
@php $id = $attributes->get('id', $name); @endphp
<div class="grid gap-1.5">
    @if ($label)
        <label for="{{ $id }}" class="text-sm font-medium text-ink">{{ $label }}</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class(['min-h-24 rounded-xl bg-white px-4 py-2.5 text-base text-ink ring-1 ring-inset ring-muted focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-rose aria-invalid:ring-2 aria-invalid:ring-danger']) }}>{{ $slot }}</textarea>
    @if ($error)
        <p id="{{ $id }}-error" role="alert" class="text-base font-semibold text-danger">{{ $error }}</p>
    @endif
</div>
