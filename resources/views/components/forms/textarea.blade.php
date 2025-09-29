@props([
    'id',
    'label',
    'model',
    'form' => [],
    'required' => false,
    'viewOnly' => false,
    'textAlign' => null,
    'maxlength' => null,
    'colspan' => 'col-span-1',
    'placeholder' => null,
    'rows' => 4,
])

@php
    $fieldKey = str($model)->replace('form.', '');
    $value = data_get($form, $fieldKey);
@endphp

<div class="flex flex-col {{ $colspan }}">
    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-400 dark:text-neutral-400' : 'text-gray-700 dark:text-white' }} mb-1">

        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {!! str($label)->contains('|')
            ? explode('|', $label)[0] . ' <span class="text-xs text-gray-500">(' . explode('|', $label)[1] . ')</span>'
            : $label !!}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-black dark:text-white ms-1">
            {{ $value ?? '—' }}
        </div>
    @else
        <textarea id="{{ $id }}" wire:model.defer="{{ $model }}" rows="{{ $rows }}"
            class="mt-1 block w-full px-4 py-2 rounded-md text-sm border
                dark:bg-neutral-700 dark:text-white dark:placeholder-gray-400
                {{ $textAlign ? 'text-' . $textAlign : '' }}
                @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                @else border-gray-300  focus:ring-indigo-500 focus:border-indigo-500 @enderror"
            {{ $maxlength ? "maxlength=$maxlength" : '' }} {{ $placeholder ? "placeholder=$placeholder" : '' }}
            {{ $required ? 'required' : '' }}>{{ $value }}</textarea>

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror
    @endif
</div>
