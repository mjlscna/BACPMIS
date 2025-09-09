@props([
    'form' => [],
    'model' => '',
    'showTable' => true,
    'page' => 1,
    'perPage' => 5,
])

@php
    // Extract full items array
    $allItems = data_get($form, str_replace('form.', '', $model), []);
    $totalItems = count($allItems);

    // 🔁 Reverse items to match create flow (newest first)
    $allItems = array_reverse($allItems);

    // Pagination logic
    $offset = ($page - 1) * $perPage;
    $items = array_slice($allItems, $offset, $perPage);

    $totalPages = ceil($totalItems / $perPage);
@endphp

@if ($showTable)
    <div class="overflow-x-auto">
        <table class="divide-y divide-gray-200 rounded-xl w-full">
            <thead class="bg-gray-50 sticky top-0 z-40">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-20">
                        Item No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Description
                    </th>
                    <th class="px-6 py-3 w-12"></th> <!-- Delete column -->
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($items as $index => $item)
                    <tr wire:key="item-{{ $offset + $index }}">
                        <td class="px-6 py-4 text-center text-sm text-gray-800">
                            <input type="text"
                                class="border border-gray-300 rounded-lg px-2 py-1 w-20 text-center focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="#" wire:model.defer="{{ $model }}.{{ $offset + $index }}.item_no">
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-800">
                            <input type="text"
                                class="border border-gray-300 rounded-lg px-2 py-1 w-full focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Item description"
                                wire:model.defer="{{ $model }}.{{ $offset + $index }}.description">
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" 
    class="text-red-500 hover:text-red-700"
    wire:click="removeItem({{ $offset + $index }})">
    ❌
</button>

                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    {{-- Pagination controls --}}
    @if ($totalPages > 1)
        <div class="flex justify-center items-center gap-2 mt-4 text-sm">
            <!-- Previous Button -->
            <button type="button" class="px-2 py-1 border rounded-lg disabled:opacity-50"
                wire:click="$set('page', {{ max(1, $page - 1) }})" @disabled($page <= 1)>
                <x-heroicon-o-chevron-left class="w-4 h-4" />
            </button>

            <!-- Page Numbers -->
            <div class="flex gap-1">
                @for ($i = 1; $i <= $totalPages; $i++)
                    <button type="button"
                        class="px-3 py-1 border rounded-lg
                        {{ $page === $i ? 'bg-emerald-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}"
                        wire:click="$set('page', {{ $i }})">
                        {{ $i }}
                    </button>
                @endfor
            </div>

            <!-- Next Button -->
            <button type="button" class="px-2 py-1 border rounded-lg disabled:opacity-50"
                wire:click="$set('page', {{ min($totalPages, $page + 1) }})" @disabled($page >= $totalPages)>
                <x-heroicon-o-chevron-right class="w-4 h-4" />
            </button>
        </div>
    @endif


@endif
