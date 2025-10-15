@props([
    'id',
    'label',
    'model',
    'required' => false,
    'viewOnly' => false,
    'colspan' => 'col-span-full',
    'form' => [],
    'helpText' => null,
    'disabled' => false,
])

@php
    // This correctly gets the boolean value from your Livewire component's form array
$value = (bool) data_get($form, str($model)->replace('form.', ''));
@endphp

<div class="{{ $colspan }}">
    @if ($viewOnly)
        <div class="flex flex-col">
            <label for="{{ $id }}" class="block text-sm font-medium text-gray-500 dark:text-neutral-400 mb-1">
                {{ $label }}
            </label>
            <div class="text-sm font-semibold text-gray-900 dark:text-white ms-1">
                {{ $value ? 'Yes' : 'No' }}
            </div>
        </div>
    @else
        <div class="flex items-center">
            <label for="{{ $id }}" class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model.defer="{{ $model }}" id="{{ $id }}"
                    class="sr-only peer" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }}>
                <div
                    class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 dark:bg-gray-700
                            peer-checked:after:translate-x-full peer-checked:after:border-white
                            after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                            dark:border-gray-600 peer-checked:bg-emerald-600">
                </div>
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                    {!! str($label)->contains('|')
                        ? explode('|', $label)[0] . ' <span class="text-xs text-gray-500">(' . explode('|', $label)[1] . ')</span>'
                        : $label !!}
                    @if ($required)
                        <span class="text-red-500 ms-1">*</span>
                    @endif
                </span>
            </label>
        </div>

        @error($model)
            <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
        @enderror

        @if ($helpText)
            <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
        @endif
    @endif
</div>
