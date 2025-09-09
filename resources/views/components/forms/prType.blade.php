@props([
    'model' => 'form.procurement_type',
    'form' => [],
    'viewOnly' => false,
    'clickable' => true,
])

<div class="flex flex-col col-span-4">
    <div class="flex flex-row items-center gap-x-4">
        <label class="text-sm font-medium {{ $clickable ? 'text-gray-700 dark:text-gray-200' : 'text-gray-500' }}">
            Per Item?
        </label>

        <label for="procurement-type-toggle" class="relative inline-block w-16 h-8 cursor-pointer">
            <input type="checkbox" class="peer sr-only" id="procurement-type-toggle"
                @change="
                    if({{ $clickable ? 'true' : 'false' }}) {
                        const val = $event.target.checked ? 'perItem' : 'perLot';
                        @this.set('{{ $model }}', val);
                    }
                "
                {{ data_get($form, 'procurement_type') === 'perItem' ? 'checked' : '' }}
                {{ $clickable ? '' : 'disabled' }}  {{-- disables the checkbox if not clickable --}}
            >

            <!-- Track -->
            <span class="absolute inset-0 bg-red-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>

            <!-- Thumb -->
            <span class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-[2rem]"></span>

            <!-- Left Label -->
            <span class="absolute top-1/2 start-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-red-600 peer-checked:text-gray-500">
                No
            </span>

            <!-- Right Label -->
            <span class="absolute top-1/2 end-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-emerald-600">
                Yes
            </span>
        </label>
    </div>
</div>
