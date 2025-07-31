@props([
    'id',
    'label',
    'model',
    'form' => [],
    'viewOnly' => false,
    'required' => false,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'colspan' => '',
    'wireModifier' => 'live',
])

@php
    $fieldKey = str($model)->replace('form.', '');
    $value = data_get($form, $fieldKey);
    $isAssoc = is_array($options) && array_keys($options) !== range(0, count($options) - 1);

    if ($isAssoc) {
        $displayValue = $options[$value] ?? '—';
    } else {
        $match = collect($options)->firstWhere($optionValue, $value);
        $displayValue = $match[$optionLabel] ?? '—';
    }
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
            {{ $displayValue }}
        </div>
    @else
        <select id="{{ $id }}" wire:model.{{ $wireModifier }}="{{ $model }}"
            class="mt-1 block w-full px-3 py-2 border rounded-md text-sm
                @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror"
            {{ $required ? 'required' : '' }}>

            <option value="">Select</option>

            @if ($isAssoc)
                @foreach ($options as $key => $text)
                    <option value="{{ $key }}">{{ $text }}</option>
                @endforeach
            @else
                @foreach ($options as $option)
                    <option value="{{ $option[$optionValue] }}">{{ $option[$optionLabel] }}</option>
                @endforeach
            @endif
        </select>

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror
    @endif
</div>
