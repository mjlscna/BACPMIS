@props([
    'id' => null,
    'label',
    'model',
    'form' => [],
    'required' => false,
    'disabled' => false,
    'viewOnly' => false,
    'colspan' => '',
])

@php
    use Illuminate\Support\Str;

    // fallback id
    if (!$id) {
        $id = 'abc-' . Str::slug($label ?? $model);
    }

    $isPerItem = data_get($form, 'procurement_type') === 'perItem';
    $initialTotal = $isPerItem ? collect(data_get($form, 'items', []))->sum('amount') : data_get($form, $model, 0);
@endphp

<div class="flex flex-col {{ $colspan }}" x-data="{
    total: {{ $initialTotal }},
    display: '{{ number_format($initialTotal, 2, '.', ',') }}',
    isPerItem: {{ $isPerItem ? 'true' : 'false' }},
    formatNumber(num) {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }
}" x-init="if (isPerItem) {
    // Initial sum
    let items = $wire.get('form.items') || [];
    total = Object.values(items).reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    display = formatNumber(total);

    // Watch for changes in items
    $watch('$wire.form.items', (items) => {
        total = Object.values(items || {}).reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
        display = formatNumber(total);
    });
} else {
    // Regular input
    total = $wire.get('{{ $model }}') || 0;
    display = formatNumber(total);
}">
    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700 dark:text-gray-200' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {!! $label !!}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white ms-3">
            ₱ <span x-text="formatNumber(total)"></span>
        </div>
    @else
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>

            <input type="text" id="{{ $id }}" x-model="display"
                @input="
                    if (!isPerItem && !{{ $disabled ? 'true' : 'false' }}) {
                        display = $event.target.value.replace(/[^0-9.]/g, '');
                        let num = parseFloat(display.replace(/,/g, ''));
                        total = isNaN(num) ? 0 : num;
                        $wire.set('{{ $model }}', total);
                    }
                "
                @blur="display = formatNumber(total)" :readonly="{{ $disabled ? 'true' : 'false' }}"
                class="mt-1 block w-full pl-8 pr-3 py-2 rounded-md text-sm text-right border
                       @error($model) border-red-500 focus:ring-red-500 focus:border-red-500
                       @else border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @enderror
                       {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                inputmode="decimal" {{ $required && !$disabled ? 'required' : '' }} />
            @error($model)
                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>
    @endif
</div>
