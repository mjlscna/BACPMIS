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

    if (!$id) {
        $id = 'abc-' . Str::slug($label ?? $model);
    }

    // strip "form." if dev accidentally passes it
    $lookup = Str::startsWith($model, 'form.') ? Str::after($model, 'form.') : $model;

    $initialValue = data_get($form, $lookup, 0);

    // Always display two decimals to match the decimal(15,2) column.
    $initialDisplay = number_format((float) $initialValue, 2, '.', ',');
@endphp

<div class="flex flex-col {{ $colspan }}">
    <label for="{{ $id }}"
        class="block text-sm font-medium mb-1
              {{ $viewOnly ? 'text-gray-500 dark:text-white' : 'text-gray-700 dark:text-white' }}">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {!! $label !!}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900 dark:text-white">₱
            {{ number_format($initialValue, 2, '.', ',') }}</div>
    @else
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-white">₱</span>
            <input type="text" inputmode="decimal" id="{{ $id }}" x-data="{ display: '{{ $initialDisplay }}' }" x-model="display"
                @input="
                    let raw = $event.target.value.replace(/[^0-9.]/g, '');
                    let dot = raw.indexOf('.');
                    let intDigits = (dot === -1 ? raw : raw.slice(0, dot)).replace(/\D/g, '');
                    let decDigits = (dot === -1 ? '' : raw.slice(dot + 1).replace(/\D/g, '').slice(0, 2));
                    let grouped = intDigits ? parseInt(intDigits, 10).toLocaleString('en-US') : (dot !== -1 ? '0' : '');
                    display = grouped + (dot !== -1 ? '.' + decDigits : '');
                    $wire.set('form.{{ $lookup }}', parseFloat((intDigits || '0') + '.' + (decDigits || '0')) || 0);
                "
                @blur="
                    let amount = parseFloat(String(display).replace(/[^0-9.]/g, '')) || 0;
                    display = amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    $wire.set('form.{{ $lookup }}', amount);
                "
                class="mt-1 block w-full pl-8 pr-3 py-2 rounded-md text-sm text-right border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white {{ $disabled ? 'bg-gray-100 dark:bg-neutral-700 cursor-not-allowed' : '' }}"
                :readonly="{{ $disabled ? 'true' : 'false' }}" {{ $required && !$disabled ? 'required' : '' }} />
        </div>
    @endif
</div>
