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
    <label for="early_procurement_switch" class="relative inline-block w-14 h-8 cursor-pointer">
        <input type="checkbox" id="early_procurement_switch" class="peer sr-only" wire:model.live="{{ $model }}"
            @unless($clickable) disabled @endunless>

        <span
            class="absolute inset-0 bg-red-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>
        <span
            class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-[1.5rem]"></span>

        <!-- Left Icon -->
        <span
            class="absolute top-1/2 start-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-red-500 peer-checked:text-white">
            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"></path>
                <path d="m6 6 12 12"></path>
            </svg>
        </span>

        <!-- Right Icon -->
        <span
            class="absolute top-1/2 end-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-emerald-600">
            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </span>
    </label>
    @endif
</div>
