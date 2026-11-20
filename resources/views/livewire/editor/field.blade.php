{{-- One editor field. $path is the wire:model path (form.people.0.name), $field comes from StepFields, $current is its value. --}}
@php
    $id = 'f-'.str_replace('.', '-', $path);
    $error = $errors->first($path);
    $type = $field['type'];
    $hint = $field['hint'] ?? null;
    $inputType = match ($type) { 'datetime' => 'datetime-local', 'number' => 'number', 'date' => 'date', 'url' => 'url', default => 'text' };
@endphp
<div data-field="{{ $path }}">
    @if ($type === 'checkbox')
        <div class="grid gap-1.5">
            <label for="{{ $id }}" class="inline-flex min-h-11 items-center gap-3 text-base text-ink">
                <input type="checkbox" id="{{ $id }}" wire:model.live="{{ $path }}" @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                    class="size-5 shrink-0 accent-rose focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-rose">
                <span>{{ $field['label'] }}</span>
            </label>
            @if ($error)<p id="{{ $id }}-error" role="alert" class="text-sm font-semibold text-danger">{{ $error }}</p>@endif
        </div>
    @elseif ($type === 'select')
        <x-ui.select :name="$path" :id="$id" :label="$field['label']" :error="$error" wire:model.live="{{ $path }}">
            @foreach ($field['options'] as $value => $label)
                <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $label }}</option>
            @endforeach
        </x-ui.select>
    @elseif ($type === 'textarea')
        <x-ui.textarea :name="$path" :id="$id" :label="$field['label']" :error="$error" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}" wire:model.live.blur="{{ $path }}" />
    @else
        <x-ui.field :name="$path" :label="$field['label']" :hint="$hint">
            <x-ui.input :type="$inputType" autocomplete="off" wire:model.live.blur="{{ $path }}" :step="$type === 'number' ? 'any' : null" :inputmode="$type === 'number' ? 'decimal' : null" />
        </x-ui.field>
    @endif
</div>
