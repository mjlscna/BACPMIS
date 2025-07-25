@props([
    'id',
    'label',
    'model',
    'required' => false,
    'viewOnly' => false,
    'textRight' => false,
    'type' => 'text',
    'maxlength' => null,
    'colspan' => 'col-span-1',
    'form' => [],
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
        <input type="{{ $type }}" id="{{ $id }}" wire:model.defer="{{ $model }}"
            {{ $maxlength ? "maxlength=$maxlength" : '' }}
            class="mt-1 block w-full px-3 py-2 rounded-md text-sm border {{ $textRight ? 'text-right' : '' }}
            @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
            @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror"
            {{ !$viewOnly && $required ? 'required' : '' }} />

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror
    @endif
</div>
