@props([
    'form' => [],
    'model' => '',
    'showTable' => true,
    'page' => 1,
    'perPage' => 5,
    'viewOnly' => false,
])

@php
    $allItems = data_get($form, str_replace('form.', '', $model), []);
    $totalItems = count($allItems);
    $offset = ($page - 1) * $perPage;
    $items = collect($allItems)->slice($offset, $perPage);

    $totalPages = ceil($totalItems / $perPage);
@endphp

@if ($showTable || $viewOnly)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-xl">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-center text-[10px] md:text-xs font-medium text-gray-500 uppercase w-16 md:w-20">
                        Item No</th>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-xs font-medium text-gray-500 uppercase">
                        Description</th>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-left text-[10px] md:text-xs font-medium text-gray-500 uppercase">
                        Amount</th>
                    @unless ($viewOnly)
                        <th class="px-2 md:px-6 py-2 md:py-3 w-10 md:w-12"></th>
                    @endunless
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($items as $originalIndex => $item)
                    @php
                        $rowIndex = $offset + $originalIndex;
                        $rowKey = 'item-' . $rowIndex . '-' . ($item['uid'] ?? uniqid());
                    @endphp
                    <tr wire:key="{{ $rowKey }}">


                        <!-- Item No -->
                        <td class="px-3 py-2">
                            <input type="text"
                                class="mt-1 block w-full px-2 py-1 rounded-md bg-gray-100 cursor-not-allowed text-center text-gray-700 border border-gray-300 text-sm"
                                value="{{ $item['item_no'] ?? '' }}" disabled>
                        </td>

                        <!-- Description -->
                        <td class="px-3 py-2">
                            @if ($viewOnly)
                                <div class="text-gray-700 text-sm">{{ $item['description'] ?? '' }}</div>
                            @else
                                <input type="text"
                                    wire:model.defer="{{ $model }}.{{ $rowIndex }}.description"
                                    class="border border-gray-300 rounded-lg px-2 py-1 w-full text-xs md:text-sm focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Item description">
                            @endif
                        </td>

                        <!-- Amount -->
                        <td class="px-3 py-2">
                            <div x-data="{
                                display: '{{ isset($item['amount']) && is_numeric($item['amount']) ? number_format($item['amount'], 2, '.', ',') : '0.00' }}',
                                formatNumber(num) {
                                    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
                                }
                            }" x-init="$watch('$wire.{{ $model }}.{{ $rowIndex }}.amount', value => {
                                display = formatNumber(parseFloat(value || 0));
                            })">

                                @if ($viewOnly)
                                    <div class="text-right text-sm">{{ display }}</div>
                                @else
                                    <input type="text"
                                        class="text-right border border-gray-300 rounded-lg px-2 py-1 w-full text-xs md:text-sm focus:ring-emerald-500 focus:border-emerald-500"
                                        x-model="display" @input="display = $event.target.value.replace(/[^0-9.]/g, '')"
                                        @blur="
                                let num = parseFloat(display.replace(/,/g, ''));
                                if (!isNaN(num)) {
                                    display = formatNumber(num);
                                    $wire.set('{{ $model }}.{{ $rowIndex }}.amount', num);
                                } else {
                                    display = '0.00';
                                    $wire.set('{{ $model }}.{{ $rowIndex }}.amount', 0);
                                }
                            "
                                        inputmode="decimal" />
                                @endif
                            </div>
                        </td>

                        <!-- Remove button -->
                        @unless ($viewOnly)
                            <td class="px-2 py-2 text-center">
                                <button type="button" class="text-red-500 hover:text-red-700"
                                    wire:click="removeItem({{ $rowIndex }})">❌</button>
                            </td>
                        @endunless
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    {{-- Pagination --}}
    @if ($totalPages > 1)
        <div class="flex justify-center items-center gap-1 md:gap-2 mt-3 md:mt-4 text-xs md:text-sm">
            <button type="button" class="px-2 py-1 border rounded-lg disabled:opacity-50"
                wire:click="$set('page', {{ max(1, $page - 1) }})" @disabled($page <= 1)>
                <x-heroicon-o-chevron-left class="w-3 h-3 md:w-4 md:h-4" />
            </button>

            <div class="flex gap-1">
                @for ($i = 1; $i <= $totalPages; $i++)
                    <button type="button"
                        class="px-2 md:px-3 py-1 border rounded-lg {{ $page === $i ? 'bg-emerald-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}"
                        wire:click="$set('page', {{ $i }})">
                        {{ $i }}
                    </button>
                @endfor
            </div>

            <button type="button" class="px-2 py-1 border rounded-lg disabled:opacity-50"
                wire:click="$set('page', {{ min($totalPages, $page + 1) }})" @disabled($page >= $totalPages)>
                <x-heroicon-o-chevron-right class="w-3 h-3 md:w-4 md:h-4" />
            </button>
        </div>
    @endif
@endif
