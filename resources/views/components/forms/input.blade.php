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
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500 dark:text-neutral-400' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {!! str($label)->contains('|')
            ? explode('|', $label)[0] . ' <span class="text-xs text-gray-500">(' . explode('|', $label)[1] . ')</span>'
            : $label !!}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            {{ $value ?? '-' }}
        </div>
    @else
        <input type="{{ $type }}" id="{{ $id }}" wire:model.defer="{{ $model }}"
            value="{{ $value }}"
            class="mt-1 block w-full px-4 py-2 rounded-md text-sm border
                {{ $readonly ? 'bg-gray-100 dark:bg-neutral-800 cursor-not-allowed' : '' }}
                {{ $textAlign ? 'text-' . $textAlign : '' }}
                @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror
                dark:text-white dark:placeholder-gray-400"
            {{ $maxlength ? "maxlength=$maxlength" : '' }} {{ $placeholder ? "placeholder=$placeholder" : '' }}
            {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }} {{ $autofocus ? 'autofocus' : '' }}
            {{ $autocomplete ? "autocomplete=$autocomplete" : '' }} {{ $step ? "step=$step" : '' }}
            {{ $min !== null ? "min=$min" : '' }} {{ $max !== null ? "max=$max" : '' }}
            {{ $required && !$viewOnly ? 'required' : '' }} />

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror

        @if ($helpText)
            <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
        @endif
    @endif
</div>
