@props([
    'id',
    'label',
    'model',
    'form' => [],
    'type' => 'text',
    'required' => false,
    'viewOnly' => false,
    'textAlign' => null,
    'maxlength' => null,
    'colspan' => 'col-span-1',
    'placeholder' => null,
    'step' => null,
    'min' => null,
    'max' => null,
])

@php
    $fieldKey = str($model)->replace('form.', '');
    $value = data_get($form, $fieldKey);
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
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-1">
            {{ $value ?? '—' }}
        </div>
    @else
        <input id="{{ $id }}" type="{{ $type }}" wire:model.defer="{{ $model }}"
            class="mt-1 block w-full px-4 py-2 rounded-md text-sm border
                {{ $textAlign ? 'text-' . $textAlign : '' }}
                @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror"
            {{ $maxlength ? "maxlength=$maxlength" : '' }} {{ $placeholder ? "placeholder=$placeholder" : '' }}
            {{ $step ? "step=$step" : '' }} {{ $min !== null ? "min=$min" : '' }}
            {{ $max !== null ? "max=$max" : '' }} {{ $required ? 'required' : '' }} />

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror
    @endif
</div>
