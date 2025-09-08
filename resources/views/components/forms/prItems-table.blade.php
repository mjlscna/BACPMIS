@props([
    'form' => [], // The full form array
    'model' => '',
    'showTable' => true,
])

@php
    // Get the items from the form dynamically using the model path
    $items = data_get($form, str_replace('form.', '', $model), []);
@endphp

@if ($showTable)
    <div class="overflow-x-auto">
        <table class="divide-y divide-gray-200 rounded-xl w-full">
            <thead class="bg-gray-50 sticky top-0 z-40">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-20">
                        Item No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($items as $index => $item)
                    <tr wire:key="item-{{ $index }}">
                        <td class="px-6 py-4 text-center text-sm text-gray-800">
                            <input type="text"
                                class="border border-gray-300 rounded-lg px-2 py-1 w-w-30 text-center focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="#" wire:model.defer="{{ $model }}.{{ $index }}.item_no">
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-800">
                            <input type="text"
                                class="border border-gray-300 rounded-lg px-2 py-1 w-full focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Item description"
                                wire:model.defer="{{ $model }}.{{ $index }}.description">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
