{{-- resources/views/components/forms/file.blade.php --}}
@props([
    'id',
    'label',
    'model',
    'required' => false,
    'viewOnly' => false,
    'colspan' => 'col-span-1',
    'form' => [],
    'helpText' => null,
    'accept' => null,
])

@php
    $value = data_get($form, str($model)->replace('form.', ''), $this->getPropertyValue($model));
    $displayName = '-';
    if (is_object($value) && method_exists($value, 'getClientOriginalName')) {
        $displayName = $value->getClientOriginalName();
    } elseif (is_string($value)) {
        $displayName = basename($value);
    }
@endphp

<div class="flex flex-col {{ $colspan }}">
    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500 dark:text-neutral-400' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {{ $label }}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            {{ $displayName }}
        </div>
    @else
        <div class="mt-1">
            <input type="file" id="{{ $id }}" wire:model="{{ $model }}"
                {{ $accept ? "accept=$accept" : '' }}
                class="block w-full text-sm text-gray-500 dark:text-neutral-400 border rounded-md cursor-pointer
                @error($model)
                    border-red-500 focus:ring-red-500 focus:border-red-500
                @else
                    {{-- Conditionally add green border on success --}}
                    {{ $value ? 'border-green-500 focus:ring-green-500 focus:border-green-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500' }}
                @enderror

                file:mr-4 file:py-2 file:px-4
                file:rounded-l-md file:border-0
                file:text-sm file:font-semibold
                file:bg-gray-100 dark:file:bg-neutral-600
                file:text-gray-700 dark:file:text-neutral-200
                hover:file:bg-gray-200 dark:hover:file:bg-neutral-500" />

            {{-- Uploading spinner --}}
            <div wire:loading wire:target="{{ $model }}" class="mt-1 text-sm text-gray-500">
                Uploading...
            </div>

            {{-- Success indicator --}}
            @if ($value && !$errors->has($model))
                <div wire:loading.remove wire:target="{{ $model }}"
                    class="mt-1 flex items-center text-sm text-green-600">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>{{ $displayName }} uploaded successfully.</span>
                </div>
            @endif

            @error($model)
                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
            @enderror

            @if ($helpText)
                <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
            @endif
        </div>
    @endif
</div>
