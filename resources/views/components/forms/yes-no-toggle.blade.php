@props([
    'id',
    'label',
    'model',
    'form' => [],
    'viewOnly' => false,
    'required' => false,
    'disabled' => false,
    'colspan' => 'col-span-full',
    'helpText' => null,
    'wireModifier' => 'live',
])

@php
    $value = (bool) data_get($form, str($model)->replace('form.', ''));
@endphp

<div class="{{ $colspan }}">
    @if ($viewOnly)
        <div class="flex items-center space-x-4">
            <label class="block text-sm font-medium text-gray-500 dark:text-neutral-400">
                {{ $label }}
            </label>
            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $value ? 'Yes' : 'No' }}
            </div>
        </div>
    @else
        <div class="flex flex-col">
            <label for="{{ $id }}" class="flex items-center cursor-pointer">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 mr-4">
                    {!! str($label)->contains('|')
                        ? explode('|', $label)[0] . ' <span class="text-xs text-gray-500">(' . explode('|', $label)[1] . ')</span>'
                        : $label !!}
                    @if ($required)
                        <span class="text-red-500 ml-1">*</span>
                    @endif
                </span>

                <div class="relative inline-block w-16 h-8">
                    <input type="checkbox" id="{{ $id }}" class="peer sr-only"
                        wire:model.{{ $wireModifier }}="{{ $model }}"
                        @if ($disabled) disabled @endif>

                    <span
                        class="absolute inset-0 rounded-full transition-colors duration-200 ease-in-out
                        peer-checked:bg-emerald-600 bg-red-500
                        @if ($disabled) opacity-50 @endif">
                    </span>

                    <span
                        class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-[2rem]"></span>

                    <span
                        class="absolute inset-y-0 start-1.5 flex justify-center items-center text-sm font-semibold text-red-600 peer-checked:text-gray-500">
                        No
                    </span>

                    <span
                        class="absolute inset-y-0 end-1.5 flex justify-center items-center text-sm font-semibold text-gray-500 peer-checked:text-emerald-600">
                        Yes
                    </span>
                </div>
            </label>

            @error($model)
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($helpText)
                <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
            @endif
        </div>
    @endif
</div>
