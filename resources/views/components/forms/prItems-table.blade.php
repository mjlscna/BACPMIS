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

    // ✨ CHANGED: Add ->reverse() here to change the display order
    $items = collect($allItems)->reverse()->slice($offset, $perPage);

    $totalPages = ceil($totalItems / $perPage);
@endphp

@if ($showTable || $viewOnly)
    <div class="overflow-x-auto ">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 rounded-xl">
            <thead class="bg-gray-200 dark:bg-neutral-900">
                <tr>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-16 md:w-20">
                        No.
                    </th>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Description
                    </th>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Amount
                    </th>
                    @unless ($viewOnly)
                        <th class="px-2 md:px-6 py-2 md:py-3 w-10 md:w-12"></th>
                    @endunless
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-neutral-700 divide-y divide-gray-200 dark:divide-gray-200">
                @foreach ($items as $rowIndex => $item)
                    <tr wire:key="item-{{ $item['uid'] ?? $item['prItemID'] }}">

                        <td class="px-3 py-2">
                            @if ($viewOnly)
                                <div class="text-gray-700 dark:text-white text-center text-sm">
                                    {{ $item['item_no'] ?? '' }}</div>
                            @else
                                <input type="text"
                                    class="mt-1 block w-full px-2 py-1 rounded-md text-center text-sm border bg-white text-gray-700 border-gray-300 cursor-not-allowed dark:bg-neutral-700 dark:text-white dark:border-gray-200"
                                    value="{{ $item['item_no'] ?? '' }}" disabled>
                            @endif
                        </td>

                        <td class="px-3 py-2">
                            @if ($viewOnly)
                                <div class="text-gray-700 dark:text-white text-sm">{{ $item['description'] ?? '' }}
                                </div>
                            @else
                                <input type="text"
                                    wire:model.defer="{{ $model }}.{{ $rowIndex }}.description"
                                    class="border border-gray-300 rounded-lg px-2 py-1 w-full text-xs md:text-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-700 dark:text-white dark:border-gray-200"
                                    placeholder="Item description">
                            @endif
                        </td>

                        <td class="px-3 py-2">
                            <div x-data="{
                                display: '{{ isset($item['amount']) && is_numeric($item['amount']) ? number_format($item['amount'], 2, '.', ',') : '0.00' }}',
                                formatNumber(num) { /* ... AlpineJS logic ... */ }
                            }" x-init="/* ... AlpineJS logic ... */">
                                @if ($viewOnly)
                                    <div class="text-right text-gray-700 dark:text-white text-sm">
                                        {{ is_numeric($item['amount'] ?? null) ? number_format($item['amount'], 2, '.', ',') : '0.00' }}
                                    </div>
                                @else
                                    <input type="text"
                                        class="text-right border border-gray-300 rounded-lg px-2 py-1 w-full text-xs md:text-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-700 dark:text-white dark:border-gray-200"
                                        x-model="display" @input="..."
                                        @blur="$wire.set('{{ $model }}.{{ $rowIndex }}.amount', display)"
                                        inputmode="decimal" />
                                @endif
                            </div>
                        </td>

                        @unless ($viewOnly)
                            <td class="px-2 py-2 text-center">
                                <button type="button" class="text-red-500 hover:text-red-700 dark:hover:text-red-400"
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
                < </button>

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
                        >
                    </button>
        </div>
    @endif
@endif
