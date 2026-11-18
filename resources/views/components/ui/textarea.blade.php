@props(['name', 'label' => null, 'error' => null])
@php $id = $attributes->get('id', $name); @endphp
<div class="grid gap-1.5">
    @if ($label)
        <label for="{{ $id }}" class="text-sm font-medium text-ink">{{ $label }}</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class(['min-h-24 rounded-xl border border-ink/20 bg-white px-3.5 py-2.5 text-sm text-ink focus:border-rose']) }}>{{ $slot }}</textarea>
    @if ($error)
        <p id="{{ $id }}-error" role="alert" class="text-xs text-danger">{{ $error }}</p>
    @endif
</div>
