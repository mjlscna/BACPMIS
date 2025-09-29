@props([
    'model' => 'form.procurement_type',
    'form' => [],
    'viewOnly' => false,
    'clickable' => true,
])

<div class="flex flex-col col-span-4">
    <div class="flex flex-row items-center gap-x-4">
        <label
            class="text-sm font-medium {{ $clickable && !$viewOnly ? 'text-gray-700 dark:text-neutral-400' : 'text-gray-500 dark:text-neutral-400' }}">
            Type:
        </label>

        @if ($viewOnly || !$clickable)
            {{-- View-only display --}}
            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ data_get($form, 'procurement_type') === 'perItem' ? 'Per Item' : 'Per Lot' }}
            </div>
        @else
            {{-- Interactive toggle --}}
            <div class="flex items-center gap-1">
                <!-- Per Lot Label -->
                <span class="text-sm font-medium text-gray-700 dark:text-white">Per Lot</span>

                <!-- Toggle Switch -->
                <label for="procurement-type-toggle" class="relative inline-block w-12 h-6 cursor-pointer">
                    <input type="checkbox" class="peer sr-only" id="procurement-type-toggle"
                        @change="
                            const val = $event.target.checked ? 'perItem' : 'perLot';
                            @this.set('{{ $model }}', val);
                        "
                        {{ data_get($form, 'procurement_type') === 'perItem' ? 'checked' : '' }}>

                    <!-- Track -->
                    <span
                        class="absolute inset-0 bg-blue-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>

                    <!-- Thumb -->
                    <span
                        class="absolute top-1/2 start-1 w-4 h-4 -translate-y-1/2 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-6"></span>
                </label>

                <!-- Per Item Label -->
                <span class="text-sm font-medium text-gray-700 dark:text-white">Per Item</span>
            </div>
        @endif
    </div>
</div>
