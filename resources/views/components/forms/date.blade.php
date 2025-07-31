@props([
    'id',
    'label',
    'model',
    'form' => [], // optional: for viewOnly display
    'viewOnly' => false,
    'required' => false,
    'colspan' => 'col-span-1',
    'hint' => null,
])

@php
    $value = data_get($form, str($model)->replace('form.', ''));
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col ' . $colspan]) }}>

    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {{ $label }}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            {{ $value ? \Carbon\Carbon::parse($value)->format('F j, Y') : '—' }}
        </div>
    @else
        <input type="date" id="{{ $id }}" wire:model.defer="{{ $model }}"
            {{ $required ? 'required' : '' }}
            class="mt-1 block w-full px-3 py-2 border rounded-md text-sm
                @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror"
            placeholder="MM/DD/YYYY">
    @endif

    @error($model)
        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
    @enderror
</div>
