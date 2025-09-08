@props([
    'id' => null,
    'form' => [],
    'model' => '',
    'trueValue' => true,
    'falseValue' => false,
    'checked' => false,
    'labelLeft' => null,
    'labelRight' => null,
    'disabled' => false,
    'viewOnly' => false,
    'trueColor' => 'bg-emerald-600',
    'falseColor' => 'bg-blue-600',
    'handleColor' => 'bg-white',
])

@php
    $checked = data_get($form, str_replace('form.', '', $model)) == $trueValue;
@endphp

<div class="flex items-center gap-x-2">
    @if ($labelLeft)
        <label class="text-sm text-gray-500">{{ $labelLeft }}</label>
    @endif

    @if ($viewOnly)
        <span class="text-sm">{{ $checked ? $trueValue : $falseValue }}</span>
    @else
        <label class="relative inline-block w-11 h-6 cursor-pointer">
            <input type="checkbox" id="{{ $id }}" class="peer sr-only"
                @change="$event.target.checked
                ? @this.set('{{ $model }}', @js($trueValue))
                : @this.set('{{ $model }}', @js($falseValue))"
                {{ $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}>

            <span
                class="absolute inset-0 {{ $falseColor }} rounded-full transition-colors duration-200 ease-in-out peer-checked:{{ $trueColor }}"></span>
            <span
                class="absolute top-1/2 start-0.5 -translate-y-1/2 w-5 h-5 {{ $handleColor }} rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full"></span>
        </label>
    @endif

    @if ($labelRight)
        <label class="text-sm text-gray-500">{{ $labelRight }}</label>
    @endif
</div>
