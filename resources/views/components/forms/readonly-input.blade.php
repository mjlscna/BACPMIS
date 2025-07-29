@props([
    'id',
    'label' => null,
    'model' => null,
    'value' => null,
    'form' => [],
    'viewOnly' => false,
    'required' => false,
    'type' => 'text',
    'maxlength' => null,
    'colspan' => 'col-span-4',
    'inputAttributes' => '',
])

@php
    $resolvedValue = $value ?? data_get($form, str($model)->replace('form.', ''));
@endphp

<div class="flex flex-col {{ $colspan }}">
    @if ($label)
        <label for="{{ $id }}"
            class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
            @if ($required && !$viewOnly)
                <span class="text-red-500 mr-1">*</span>
            @endif
            {{ $label }}
        </label>
    @endif

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            {{ $resolvedValue ?? '—' }}
        </div>
    @else
        <input type="{{ $type }}" id="{{ $id }}" wire:model="{{ $model }}"
            {{ $maxlength ? "maxlength=$maxlength" : '' }} readonly {!! $attributes->merge([
                'class' =>
                    $inputAttributes ?:
                    'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed text-gray-700',
            ]) !!} />
    @endif
</div>
