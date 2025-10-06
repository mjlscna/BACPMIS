<x-forms.modal title="Select Procurements" size="max-w-5xl" wire:model="showModal">
    <div
        class="w-full bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700 flex flex-col max-h-[85vh]">

        {{-- Search Header --}}
        <div
            class="sticky top-0 z-10 bg-white dark:bg-neutral-900 px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <div class="relative w-80">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search Procurements..."
                    class="px-4 pr-10 py-2 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-white" width="20"
                    height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M10.5 18.5a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-xs divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-200 dark:bg-neutral-900 sticky top-0">
                    <tr>
                        <th class="p-1 w-12"></th>
                        <th class="p-1 text-left font-semibold text-black dark:text-white w-12">PR No.</th>
                        <th class="p-1 text-left font-semibold text-black dark:text-white whitespace-nowrap w-96">
                            Program/Project</th>
                        <th class="p-1 text-center font-semibold text-black dark:text-white whitespace-nowrap w-16">
                            Division</th>
                        <th class="p-1 text-center font-semibold text-black dark:text-white w-24">ABC Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                    @forelse($procurements as $proc)
                        {{-- 'perLot' Row (Original Behavior) --}}
                        <tr wire:key="procurement-{{ $proc->id }}"
                            class="hover:bg-gray-100 dark:hover:bg-neutral-700 {{ in_array($proc->id, $selectedProcurements) ? 'bg-emerald-100 dark:bg-emerald-900' : '' }}">
                            <td class="p-2 text-center">
                                @if ($proc->procurement_type === 'perLot')
                                    <input type="checkbox" wire:model.live="selectedProcurements"
                                        value="{{ $proc->id }}"
                                        class="form-checkbox h-4 w-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 accent-emerald-600">
                                @else
                                    <button wire:click.stop="toggleItems({{ $proc->id }})"
                                        class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-neutral-600">
                                        <svg class="h-4 w-4 transition-transform {{ $expandedProcurementId === $proc->id ? 'rotate-90' : '' }}"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @endif
                            </td>
                            <td class="p-2 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $proc->pr_number }}
                            </td>
                            <td class="p-2 text-gray-900 dark:text-gray-100 w-96">
                                {{ $proc->procurement_program_project }}</td>
                            <td class="px-1 py-1 text-center text-xs text-black dark:text-white w-16">
                                {{ $proc->division->abbreviation }}</td>
                            <td class="p-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-100 w-24">
                                <span class="text-gray-500">₱</span>
                                <span>{{ number_format($proc->abc ?? 0, 2) }}</span>
                            </td>
                        </tr>

                        {{-- ADDED: Collapsible 'perItem' Row --}}
                        @if ($proc->procurement_type === 'perItem' && $expandedProcurementId === $proc->id)
                            <tr wire:key="expanded-{{ $proc->id }}">
                                <td colspan="5" class="p-0 bg-gray-50 dark:bg-neutral-800/50">
                                    <div class="p-4">
                                        <table class="w-full text-xs bg-white dark:bg-neutral-800 rounded-lg shadow">
                                            <thead class="bg-gray-100 dark:bg-neutral-900">
                                                <tr>
                                                    <th class="p-2 w-12"></th>
                                                    <th class="p-2 text-left font-semibold text-black dark:text-white">
                                                        Item Description</th>
                                                    <th class="p-2 text-right font-semibold text-black dark:text-white">
                                                        Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                                @foreach ($proc->pr_items as $item)
                                                    <tr>
                                                        <td class="p-2 text-center">
                                                            <input type="checkbox" wire:model.live="selectedItemIds"
                                                                value="{{ $item->id }}"
                                                                class="form-checkbox h-4 w-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 accent-emerald-600">
                                                        </td>
                                                        <td class="p-2">{{ $item->description }}</td>
                                                        <td class="p-2 text-right">
                                                            ₱{{ number_format($item->amount ?? 0, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No available procurements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($procurements->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between items-center mb-4">
                <button wire:click.prevent="previousPage('page')" @disabled($procurements->onFirstPage())
                    class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-300 dark:hover:text-white">
                    {!! __('pagination.previous') !!}
                </button>
                <span class="text-sm text-gray-600 dark:text-gray-400">Page {{ $procurements->currentPage() }} of
                    {{ $procurements->lastPage() }}</span>
                <button wire:click.prevent="nextPage('page')" @disabled(!$procurements->hasMorePages())
                    class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-neutral-800 dark:border-neutral-700 dark:text-gray-300 dark:hover:text-white">
                    {!! __('pagination.next') !!}
                </button>
            </nav>
        @endif

        @if ($totalSelectedCount > 0)
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">Selected Items
                    ({{ $totalSelectedCount }})</h3>

                @if ($selectedLots->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-200 dark:bg-neutral-900">
                                <tr>
                                    <th class="p-1 text-left font-semibold text-black dark:text-white w-12">PR No.</th>
                                    <th
                                        class="p-1 text-left font-semibold text-black dark:text-white whitespace-nowrap w-96">
                                        Program/Project</th>
                                    <th
                                        class="p-1 text-center font-semibold text-black dark:text-white whitespace-nowrap w-16">
                                        Division</th>
                                    <th class="p-1 text-center font-semibold text-black dark:text-white w-24">ABC Amount
                                    </th>
                                    <th class="p-1 text-center font-semibold text-black dark:text-white w-2"></th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-800">
                                @foreach ($selectedLots as $lot)
                                    <tr wire:key="selected-lot-{{ $lot->id }}">
                                        <td class="p-2 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            {{ $lot->pr_number }}</td>
                                        <td class="p-2 text-gray-900 dark:text-gray-100 w-96">
                                            {{ $lot->procurement_program_project }}</td>
                                        <td class="px-1 py-1 text-center text-xs text-black dark:text-white w-16">
                                            {{ $lot->division->abbreviation }}</td>
                                        <td
                                            class="p-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-100 w-24">
                                            <span class="text-gray-500">₱</span>
                                            <span>{{ number_format($lot->abc ?? 0, 2) }}</span>
                                        </td>
                                        <td class="p-1 text-center">
                                            <button wire:click.prevent="removeSelection({{ $lot->id }})"
                                                class="font-medium text-red-500 hover:text-red-700">X</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination for Selected Lots --}}
                    @if ($selectedLots->hasPages())
                        <div class="flex justify-between items-center mt-2">
                            <button wire:click="previousCustomPage('selectedLotsPage')" @disabled($selectedLots->onFirstPage())
                                class="px-2 py-1 text-sm border border-gray-300 rounded-md dark:border-neutral-700 disabled:opacity-50">
                                {!! __('pagination.previous') !!}
                            </button>

                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Page {{ $selectedLots->currentPage() }} of {{ $selectedLots->lastPage() }}
                            </span>

                            <button wire:click="nextCustomPage('selectedLotsPage')" @disabled(!$selectedLots->hasMorePages())
                                class="px-2 py-1 text-sm border border-gray-300 rounded-md dark:border-neutral-700 disabled:opacity-50">
                                {!! __('pagination.next') !!}
                            </button>
                        </div>
                    @endif

                @endif


                @if ($selectedItems->isNotEmpty())
                    <div class="overflow-x-auto mt-2">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-200 dark:bg-neutral-900">
                                <tr>
                                    <th class="p-1 text-left font-semibold text-black dark:text-white w-12">From PR No.
                                    </th>
                                    <th
                                        class="p-1 text-left font-semibold text-black dark:text-white whitespace-nowrap w-96">
                                        Item Description</th>
                                    <th class="p-1 text-center font-semibold text-black dark:text-white w-24">Amount
                                    </th>
                                    <th class="p-1 text-center font-semibold text-black dark:text-white w-2"></th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-800">
                                @foreach ($selectedItems as $item)
                                    <tr wire:key="selected-item-{{ $item->id }}">
                                        <td class="p-2 text-gray-500">{{ $item->procurement->pr_number }}</td>
                                        <td class="p-2 text-gray-900 dark:text-gray-100 w-96">{{ $item->description }}
                                        </td>
                                        <td
                                            class="p-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-100 w-24">
                                            <span class="text-gray-500">₱</span>
                                            <span>{{ number_format($item->amount ?? 0, 2) }}</span>
                                        </td>
                                        <td class="p-1 text-center">
                                            <button wire:click.prevent="removeItemSelection({{ $item->id }})"
                                                class="font-medium text-red-500 hover:text-red-700">X</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination for Selected Items --}}
                    @if ($selectedItems->hasPages())
                        <div class="flex justify-between items-center mt-2">
                            <button wire:click.prevent="previousPage('selectedItemsPage')" @disabled($selectedItems->onFirstPage())
                                class="px-2 py-1 text-sm border border-gray-300 rounded-md dark:border-neutral-700 disabled:opacity-50">
                                {!! __('pagination.previous') !!}
                            </button>

                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Page {{ $selectedItems->currentPage() }} of {{ $selectedItems->lastPage() }}
                            </span>

                            <button wire:click.prevent="nextPage('selectedItemsPage')" @disabled(!$selectedItems->hasMorePages())
                                class="px-2 py-1 text-sm border border-gray-300 rounded-md dark:border-neutral-700 disabled:opacity-50">
                                {!! __('pagination.next') !!}
                            </button>
                        </div>
                    @endif
                @endif

            </div>
        @endif
        {{-- Footer --}}
        <div class="p-4 border-t border-gray-200 dark:border-neutral-700">


            <div class="flex justify-end pt-2">
                <button wire:click="selectProcurements" @class([
                    'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm',
                    'text-white bg-emerald-600 hover:bg-emerald-700' => $totalSelectedCount > 0,
                    'text-gray-400 bg-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-500' =>
                        $totalSelectedCount === 0,
                ]) @disabled($totalSelectedCount === 0)>
                    Select ({{ $totalSelectedCount }}) Items
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path fill-rule="evenodd"
                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm4.28 10.28a.75.75 0 0 0 0-1.06l-3-3a.75.75 0 1 0-1.06 1.06l1.72 1.72H8.25a.75.75 0 0 0 0 1.5h5.69l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</x-forms.modal>
