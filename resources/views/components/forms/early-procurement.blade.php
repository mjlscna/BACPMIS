@props([
'model' => 'form.early_procurement',
'form' => [],
'viewOnly' => false,
'clickable' => false,
])

@php
$value = data_get($attributes->wire('model')->value, $form['early_procurement'] ?? 0);
@endphp

<div class="flex flex-col col-span-4">
    <label
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        Early Procurement
    </label>

    @if ($viewOnly)
    <div class="text-sm font-bold text-gray-900 dark:text-white ms-3">
        {{ $value ? 'Yes' : 'No' }}
    </div>
    @else
    <label for="early_procurement_switch" class="relative inline-block w-16 h-8 cursor-pointer">
        <input type="checkbox" id="early_procurement_switch" class="peer sr-only" wire:model.live="{{ $model }}"
            @unless($clickable) disabled @endunless>

        <!-- Track -->
        <span
            class="absolute inset-0 bg-red-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>
        <span
            class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-[2rem]"></span>

        <!-- Left Icon -->
        <span
            class="absolute top-1/2 start-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-red-600 peer-checked:text-gray-500">
            No
        </span>

        <!-- Right Icon -->
        <span
            class="absolute top-1/2 end-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-emerald-600">
            Yes
        </span>
    </label>
    @endif
</div>
