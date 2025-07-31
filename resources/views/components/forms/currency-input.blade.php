@props([
    'id',
    'label',
    'model',
    'form' => [],
    'required' => false,
    'viewOnly' => false,
    'colspan' => '',
    'wireModifier' => 'live', // live, defer, lazy
])

@php
    use Illuminate\Support\Str;

    $rawValue = data_get($form, Str::after($model, 'form.'), 0);
    $formattedValue = number_format((float) $rawValue, 2);

    $wireAttribute = match ($wireModifier) {
        'lazy' => 'wire:model.lazy',
        'defer' => 'wire:model.defer',
        default => 'wire:model',
    };
@endphp

<div class="flex flex-col {{ $colspan }}" x-data="{ display: '{{ $formattedValue }}' }">
    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {{ $label }}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            ₱ {{ $formattedValue }}
        </div>
    @else
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>

            <input type="text" id="{{ $id }}" x-model="display"
                @input="display = $event.target.value.replace(/[^0-9.]/g, '')"
                @blur="
                    let num = parseFloat(display.replace(/,/g, ''));
                    if (!isNaN(num)) {
                        display = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(num);
                        $wire.set('{{ $model }}', num);
                    } else {
                        display = '';
                        $wire.set('{{ $model }}', null);
                    }
                "
                class="mt-1 block w-full pl-8 pr-3 py-2 rounded-md text-sm text-right border
                    @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                    @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror"
                inputmode="decimal" {{ $required ? 'required' : '' }} />

            @error($model)
                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>
    @endif
</div>
