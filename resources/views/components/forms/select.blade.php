@props([
    'id',
    'label',
    'model',
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'form' => [],
    'viewOnly' => false,
    'required' => false,
    'colspan' => 'col-span-1',
])

@php
    $value = data_get($form, str($model)->replace('form.', ''));
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col ' . $colspan]) }}>
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
            {{ collect($options)->firstWhere($optionValue, $value)[$optionLabel] ?? '—' }}
        </div>
    @else
        <select id="{{ $id }}" wire:model.defer="{{ $model }}"
            class="mt-1 block w-full px-4 py-2 border rounded-md text-sm
        @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
        @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror"
            {{ $required ? 'required' : '' }}>
            <option value="">Select</option>
            @foreach ($options as $option)
                <option value="{{ $option[$optionValue] }}" {{ $value == $option[$optionValue] ? 'selected' : '' }}>
                    {{ $option[$optionLabel] }}
                </option>
            @endforeach
        </select>

    @endif

    @error($model)
        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
    @enderror
</div>
