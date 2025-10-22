<x-forms.flex-modal title="Select" size="max-w-5xl" wire:model="showModal">

    {{-- Search Header (Frozen at Top) --}}
    <div class="flex-shrink-0 bg-white dark:bg-neutral-900 px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
        <div class="relative w-80">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."
                class="px-4 pr-10 py-2 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-white" width="20"
                height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M10.5 18.5a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
        </div>
    </div>

    {{-- Main Content Area (Flexible) --}}
    <div class="flex-1 flex flex-col overflow-hidden min-h-0">

        {{-- First Table: Procurements (with own scroll and sticky pagination) --}}
        <div class="flex-1 flex flex-col overflow-hidden min-h-0">
            {{-- Scrollable Table Content --}}
            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-xs divide-y divide-gray-200 dark:divide-neutral-700">
                    {{-- CONDITIONAL TABLE FOR 'perLot' --}}
                    @if ($procurementType === 'perLot')
                        <thead class="sticky top-0 z-20 bg-gray-200 dark:bg-neutral-900">
                            <tr>
                                <th
                                    class="p-2 text-center font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 w-4">
                                </th>
                                <th
                                    class="p-2 text-left font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 w-5">
                                    PR No.</th>
                                <th
                                    class="p-2 text-left font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 ">
                                    Procurement Program / Project</th>
                                <th
                                    class="p-2 text-center font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 w-32">
                                    ABC Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700 p-2">
                            @forelse($results as $proc)
                                <tr wire:key="procurement-{{ $proc->id }}"
                                    wire:click="toggleSelection('lot', {{ $proc->id }})"
                                    class="hover:bg-gray-100 dark:hover:bg-neutral-700 cursor-pointer {{ in_array($proc->id, $selectedLotIds) ? 'bg-emerald-100 dark:bg-emerald-900' : '' }}">
                                    <td class="p-2 text-center" wire:click.stop>
                                        <input type="checkbox" wire:model.live="selectedLotIds"
                                            value="{{ $proc->id }}"
                                            class="form-checkbox h-4 w-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 accent-emerald-600">
                                    </td>
                                    <td class="p-2 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {{ $proc->pr_number }}</td>
                                    <td class="p-2 text-gray-900 dark:text-gray-100">
                                        {{ $proc->procurement_program_project }}</td>
                                    <td class="p-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        <span class="text-gray-500">₱</span>
                                        <span>{{ number_format($proc->abc ?? 0, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">No available procurements
                                        found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- CONDITIONAL TABLE FOR 'perItem' --}}
                    @elseif ($procurementType === 'perItem')
                        <thead class="sticky top-0 z-20 bg-gray-200 dark:bg-neutral-900">
                            <tr>
                                <th
                                    class="p-2 text-center font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 w-4">
                                </th>
                                <th
                                    class="p-2 text-left font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 w-5">
                                    PR No.</th>
                                <th
                                    class="p-2 text-left font-semibold text-black dark:text-white dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 ">
                                    Item Description</th>
                                <th
                                    class="p-2 text-center font-semibold text-black dark:text-white  dark:bg-neutral-900 border-b border-gray-300 dark:border-neutral-600 w-32">
                                    Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700 p-2">
                            @forelse($results as $item)
                                <tr wire:key="item-{{ $item->id }}"
                                    wire:click="toggleSelection('item', {{ $item->id }})"
                                    class="hover:bg-gray-100 dark:hover:bg-neutral-700 cursor-pointer {{ in_array($item->id, $selectedItemIds) ? 'bg-emerald-100 dark:bg-emerald-900' : '' }}">
                                    <td class="p-2 text-center" wire:click.stop>
                                        <input type="checkbox" wire:model.live="selectedItemIds"
                                            value="{{ $item->id }}"
                                            class="form-checkbox h-4 w-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500 accent-emerald-600">
                                    </td>
                                    <td class="p-2 text-left whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {{ $item->procurement->pr_number }}</td>
                                    <td class="p-2 text-left text-gray-900 dark:text-gray-100">
                                        {{ $item->description }}
                                    </td>
                                    <td class="p-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        <span class="text-gray-500">₱</span>
                                        <span>{{ number_format($item->amount ?? 0, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-gray-500">No available items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>

            {{-- Sticky Pagination for First Table --}}
            @if ($results && $results->hasPages())
                <div
                    class="flex-shrink-0 border-t border-gray-200 dark:border-neutral-700 px-4 py-2 bg-white dark:bg-neutral-900 grid grid-cols-3 items-center">

                    {{-- Column 1: Item Count (Left Aligned) --}}
                    <div class="text-xs text-gray-500 text-left">
                        Showing {{ $results->firstItem() }} to {{ $results->lastItem() }} of
                        {{ $results->total() }} items
                    </div>

                    {{-- Column 2: Pagination (Center Aligned) --}}
                    <nav role="navigation" aria-label="Pagination Navigation"
                        class="flex justify-center items-center gap-3">

                        {{-- Previous Button --}}
                        <button wire:click.prevent="previousPage('page')" @disabled($results->onFirstPage())
                            class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path fill-rule="evenodd"
                                    d="M10.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L12.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"
                                    clip-rule="evenodd" />
                                <path fill-rule="evenodd"
                                    d="M4.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L6.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- Page Info --}}
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $results->currentPage() }} of {{ $results->lastPage() }}
                        </span>

                        {{-- Next Button --}}
                        <button wire:click.prevent="nextPage('page')" @disabled(!$results->hasMorePages())
                            class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path fill-rule="evenodd"
                                    d="M13.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L11.69 12 4.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z"
                                    clip-rule="evenodd" />
                                <path fill-rule="evenodd"
                                    d="M19.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06L17.69 12l-6.97-6.97a.75.75 0 0 1 1.06-1.06l7.5 7.5Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>

                    {{-- Column 3: Empty Spacer (Right Aligned) --}}
                    <div></div>

                </div>
            @endif

        </div>

        {{-- Second Table: Selected PR (Unified for Lots and Items) --}}
        @if ($totalSelectedCount > 0)
            <div
                class="flex-1 flex flex-col overflow-hidden min-h-0 border-t-2 border-emerald-300 dark:border-neutral-600">
                {{-- Scrollable Selected Items Content --}}
                <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-neutral-800/50">
                    <div
                        class="sticky top-0 bg-white dark:bg-neutral-800 z-20 border-b border-gray-200 dark:border-neutral-700 pl-2">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white ">
                            Selected ({{ $totalSelectedCount }})
                        </h3>
                    </div>

                    <div>
                        <table class="w-full text-xs divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="sticky top-5 z-10 bg-gray-200 dark:bg-neutral-900">
                                <tr>
                                    <th
                                        class="p-2 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 w-20">
                                        PR No.</th>
                                    <th
                                        class="p-2 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600">
                                        @if ($procurementType === 'perLot')
                                            Procurement Program / Project
                                        @else
                                            Item Description
                                        @endif
                                    </th>
                                    <th
                                        class="p-2 text-right font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 w-32">
                                        Amount</th>
                                    <th
                                        class="p-2 text-center font-semibold text-black dark:text-white w-12 border-b border-gray-300 dark:border-neutral-600">
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 p-2">
                                @foreach ($selectedPR as $pr)
                                    <tr wire:key="selected-pr-{{ $pr['id'] }}">
                                        <td class="p-2 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $pr['pr_number'] }}
                                        </td>
                                        <td class="p-2 text-gray-900 dark:text-gray-100">
                                            {{ $pr['description'] ?? $pr['procurement_program_project'] }}
                                        </td>
                                        <td class="p-2 text-right text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            <span class="text-gray-500">₱</span>
                                            <span>{{ number_format($pr['amount'] ?? ($pr['abc'] ?? 0), 2) }}</span>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button wire:click.prevent="removeSelectedPR({{ $pr['id'] }})"
                                                class="font-medium text-red-500 hover:text-red-700 text-sm">×</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($selectedPR->isNotEmpty() && $selectedPR->hasPages())
                    <div
                        class="flex-shrink-0 border-t border-gray-200 dark:border-neutral-700 px-4 py-2 bg-white dark:bg-neutral-900 grid grid-cols-3 items-center">

                        {{-- Column 1: Item Count (Left Aligned) --}}
                        <div class="text-xs text-gray-500 text-left">
                            Showing {{ $selectedPR->firstItem() }} to {{ $selectedPR->lastItem() }} of
                            {{ $selectedPR->total() }} items
                        </div>

                        {{-- Column 2: Pagination (Center Aligned) --}}
                        <nav role="navigation" aria-label="Pagination Navigation"
                            class="flex justify-center items-center gap-3">

                            {{-- Previous Button --}}
                            <button wire:click.prevent="nextCustomPage('selectedPRPage')" @disabled(!$selectedPR->hasMorePages())
                                class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5">
                                    <path fill-rule="evenodd"
                                        d="M10.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L12.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"
                                        clip-rule="evenodd" />
                                    <path fill-rule="evenodd"
                                        d="M4.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L6.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            {{-- Page Info --}}
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $selectedPR->currentPage() }} of {{ $selectedPR->lastPage() }}
                            </span>

                            {{-- Next Button --}}
                            <button wire:click.prevent="nextCustomPage('selectedPRPage')" @disabled(!$selectedPR->hasMorePages())
                                class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5">
                                    <path fill-rule="evenodd"
                                        d="M13.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L11.69 12 4.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z"
                                        clip-rule="evenodd" />
                                    <path fill-rule="evenodd"
                                        d="M19.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06L17.69 12l-6.97-6.97a.75.75 0 0 1 1.06-1.06l7.5 7.5Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>

                        {{-- Column 3: Empty Spacer (Right Aligned) --}}
                        <div></div>

                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- Footer with Select Button (Frozen at Bottom) --}}
    <div class="flex-shrink-0 p-2 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
        <div class="flex justify-end">
            <button wire:click="selectProcurements" @class([
                'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm',
                'text-white bg-emerald-600 hover:bg-emerald-700' => $totalSelectedCount > 0,
                'text-gray-400 bg-gray-200 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-500' =>
                    $totalSelectedCount === 0,
            ]) @disabled($totalSelectedCount === 0)>
                Selected ({{ $totalSelectedCount }})
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                    <path fill-rule="evenodd"
                        d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm4.28 10.28a.75.75 0 0 0 0-1.06l-3-3a.75.75 0 1 0-1.06 1.06l1.72 1.72H8.25a.75.75 0 0 0 0 1.5h5.69l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3Z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</x-forms.flex-modal>
