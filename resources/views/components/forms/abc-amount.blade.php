@props([
    'id',
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

    // Total of all items
    $total = collect(data_get($form, 'items', []))->sum('amount');
    $formattedValue = number_format((float) $total, 2);
@endphp

<div class="flex flex-col {{ $colspan }}" x-data="{
    total: {{ $total }},
    display: '{{ number_format($total, 2, '.', ',') }}',
    formatNumber(num) {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }
}" x-init="// Watch Livewire 'form.items' array and recalc total when any amount changes
$watch('$wire.form.items', (items) => {
    total = Object.values(items || {}).reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    display = formatNumber(total);
});">
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
    @elseif($disabled)
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>
            <input type="text" id="{{ $id }}" x-model="display" readonly
                class="mt-1 block w-full pl-8 pr-3 py-2 rounded-md bg-gray-100 cursor-not-allowed text-right text-gray-700 border border-gray-300 text-sm" />
        </div>
    @else
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>
            <input type="text" id="{{ $id }}" x-model="display"
                @input="
                       // Remove non-numeric characters except dot
                       display = $event.target.value.replace(/[^0-9.]/g, '');
                       let num = parseFloat(display.replace(/,/g, ''));
                       if (!isNaN(num)) {
                           total = num;
                           $wire.set('{{ $model }}', num);
                       } else {
                           total = 0;
                           $wire.set('{{ $model }}', 0);
                       }
                       display = formatNumber(total);
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
