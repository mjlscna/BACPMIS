@props([
    'id' => null,
    'label',
    'form' => [],
    'colspan' => '',
])

@php
    use Illuminate\Support\Str;

    if (!$id) {
        $id = 'abc-item-' . Str::slug($label ?? 'abc-item');
    }

    $initialTotal = collect(data_get($form, 'items', []))->sum('amount');
@endphp

<div class="flex flex-col {{ $colspan }}" x-data="{
    total: {{ $initialTotal }},
    display: '{{ number_format($initialTotal, 2, '.', ',') }}',
    formatNumber(num) {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }
}" x-init="let items = $wire.get('form.items') || [];
total = Object.values(items).reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
display = formatNumber(total);
$wire.set('form.abc', total); // <-- push to Livewire

$watch('$wire.form.items', (items) => {
    total = Object.values(items || {}).reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    display = formatNumber(total);
    $wire.set('form.abc', total); // <-- push updated total to Livewire
});">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">{!! $label !!}</label>

    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>
        <input type="text" id="{{ $id }}" x-model="display" readonly
            class="mt-1 block w-full pl-8 pr-3 py-2 rounded-md text-sm text-right bg-gray-100 cursor-not-allowed border border-gray-300" />
    </div>
</div>
