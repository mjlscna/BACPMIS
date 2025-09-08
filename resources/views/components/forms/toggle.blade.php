@props([
    'id' => null,
    'label' => '',
    'model' => '',
    'required' => false,
    'viewOnly' => false,
    'textAlign' => null, // 'text-left', 'text-right', 'text-center'
    'type' => 'text',
    'maxlength' => null,
    'colspan' => 'col-span-1',
    'form' => [],
    'placeholder' => '',
    'disabled' => false,
    'readonly' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'step' => null,
    'min' => null,
    'max' => null,
    'helpText' => null,
    'trueColor' => 'bg-emerald-600', // toggle ON color
    'falseColor' => 'bg-blue-600', // toggle OFF color
    'handleColor' => 'bg-white', // knob color
    'trueValue' => true,
    'falseValue' => false,
    'checked' => false,
    'labelLeft' => null, // optional override
    'labelRight' => null, // optional override
])

<div class="flex items-center gap-x-2 {{ $colspan }}">
    @if ($label)
        <label class="text-sm text-gray-500 {{ $textAlign ?? '' }}">
            {{ $label }} @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    @if ($viewOnly)
        <span>{{ $checked ? $trueValue ?? 'Yes' : $falseValue ?? 'No' }}</span>
    @else
        <label class="relative inline-block w-11 h-6 cursor-pointer">
            <input type="checkbox" id="{{ $id }}" class="peer sr-only"
                @change="$event.target.checked
                        ? @this.set('{{ $model }}', @js($trueValue))
                        : @this.set('{{ $model }}', @js($falseValue))"
                {{ $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }}>
            <span
                class="absolute inset-0 {{ $falseColor }} rounded-full transition-colors duration-200 ease-in-out peer-checked:{{ $trueColor }}"></span>
            <span
                class="absolute top-1/2 start-0.5 -translate-y-1/2 w-5 h-5 {{ $handleColor }} rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full"></span>
        </label>
        @if ($labelLeft || $labelRight)
            <span class="ml-2 text-sm text-gray-500">
                {{ $labelLeft ?? $falseValue }} / {{ $labelRight ?? $trueValue }}
            </span>
        @endif
    @endif

    @if ($helpText)
        <p class="text-xs text-gray-400 mt-1 col-span-full">{{ $helpText }}</p>
    @endif
</div>
