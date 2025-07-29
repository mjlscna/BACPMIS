@props([
    'id',
    'label',
    'model',
    'form' => [],
    'required' => false,
    'viewOnly' => false,
    'rows' => 2,
    'maxlength' => null,
    'colspan' => 'col-span-full',
    'resizable' => true,
])

@php
    $fieldKey = str($model)->replace('form.', '');
    $value = data_get($form, $fieldKey, '');
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
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-0 whitespace-pre-wrap">
            {{ $value ?: '—' }}
        </div>
    @else
        <textarea id="{{ $id }}" wire:model.defer="{{ $model }}"
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif rows="{{ $rows }}"
            {{ $required ? 'required' : '' }}
            class="mt-1 block w-full px-3 py-2 rounded-md border text-sm
                {{ $resizable ? 'resize-y' : 'resize-none' }}
                @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror">
        </textarea>

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror
    @endif
</div>
