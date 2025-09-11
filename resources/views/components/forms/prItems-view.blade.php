@props(['items' => collect()])

<div class="overflow-x-auto">
    <table class="w-full border border-gray-200 rounded-xl">
        <thead class="bg-gray-50">
            <tr>
                <th
                    class="pl-2 px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-[220px] border-b border-gray-200">
                    Item No
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                    Description
                </th>
            </tr>
        </thead>
        <tbody class="bg-white">
            @forelse($items as $item)
                <tr class="border-b border-gray-200">
                    <td class="pl-4 px-2 py-2 text-left text-sm">
                        {{ $item->item_no }}
                    </td>
                    <td class="pl-4 px-3 py-2 text-sm">
                        {{ $item->description }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-3 py-3 text-center text-gray-500">
                        No items found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
