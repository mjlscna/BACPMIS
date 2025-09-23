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

    $initialValue = data_get($form, $model, 0);
@endphp

<div class="flex flex-col {{ $colspan }}">
    <label for="{{ $id }}"
        class="block text-sm font-medium {{ $viewOnly ? 'text-gray-500' : 'text-gray-700' }} mb-1">
        @if ($required && !$viewOnly)
            <span class="text-red-500 mr-1">*</span>
        @endif
        {!! $label !!}
    </label>

    @if ($viewOnly)
        <div class="text-sm font-semibold text-gray-900">₱ {{ number_format($initialValue, 2, '.', ',') }}</div>
    @else
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>
            <input type="text" id="{{ $id }}" x-data="{ display: '{{ number_format($initialValue, 2, '.', ',') }}' }" x-model="display"
                @input="
                    display = $event.target.value.replace(/[^0-9.]/g, '');
                    $wire.set('{{ $model }}', parseFloat(display) || 0);
                "
                @blur="display = parseFloat(display || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"
                class="mt-1 block w-full pl-8 pr-3 py-2 rounded-md text-sm text-right border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                :readonly="{{ $disabled ? 'true' : 'false' }}" {{ $required && !$disabled ? 'required' : '' }} />
        </div>
    @endif
</div>
