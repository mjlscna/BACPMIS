@props([
    'id',
    'label',
    'model',
    'required' => false,
    'viewOnly' => false,
    'textAlign' => null,
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
])

@php
    $value = data_get($form, str($model)->replace('form.', ''));
@endphp

<div class="flex flex-col {{ $colspan }}">
    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {{ $label }}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            {{ $value ?? '-' }}
        </div>
    @else
        <div class="relative flex items-center">
            @if ($hasSlot = $attributes->has('prepend'))
                <span class="absolute start-2 text-sm text-gray-400">
                    {{ $attributes->get('prepend') }}
                </span>
            @endif

            <input type="{{ $type }}" id="{{ $id }}" wire:model.defer="{{ $model }}"
                class="mt-1 block w-full px-4 py-2 rounded-md text-sm border {{ $textAlign ? 'text-' . $textAlign : '' }}
                    @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                    @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror
                    {{ $hasSlot ? 'ps-7' : '' }}"
                {{ $maxlength ? "maxlength=$maxlength" : '' }} {{ $placeholder ? "placeholder=$placeholder" : '' }}
                {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }}
                {{ $autofocus ? 'autofocus' : '' }} {{ $autocomplete ? "autocomplete=$autocomplete" : '' }}
                {{ $step ? "step=$step" : '' }} {{ $min !== null ? "min=$min" : '' }}
                {{ $max !== null ? "max=$max" : '' }} {{ $required && !$viewOnly ? 'required' : '' }} />

            @if ($hasSlot = $attributes->has('append'))
                <span class="absolute end-2 text-sm text-gray-400">
                    {{ $attributes->get('append') }}
                </span>
            @endif
        </div>

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror

        @if ($helpText)
            <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
        @endif
    @endif
</div>
