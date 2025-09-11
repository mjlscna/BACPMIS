<x-forms.modal title="Procurements" size="max-w-5xl" wire:model="showModal">
    <div
        class="w-full bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700 flex flex-col max-h-[80vh]">

        <!-- Header with search -->
        <div
            class="sticky top-0 z-40 bg-white dark:bg-neutral-900 px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-center gap-x-2">
                <div class="relative">
                    <input type="text" wire:model.debounce.300ms="search" placeholder="Search Procurements..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20" height="20"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 21l-4.35-4.35" />
                        <circle cx="10" cy="10" r="7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Table (scrollable body) -->
        <div class="overflow-y-auto flex-1">
            <table class="table-fixed w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 w-[120px]">
                            PR Number</th>
                        <th
                            class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 w-[100px]">
                            Type</th>
                        <th
                            class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">
                            Program/Project</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Date
                            Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse($procurements as $proc)
                        <tr wire:click="$set('selectedProcurement', {{ $proc->id }})"
                            class="hover:bg-gray-100 dark:hover:bg-neutral-800 cursor-pointer {{ isset($selectedProcurement) && $selectedProcurement === $proc->id ? 'bg-blue-50 dark:bg-blue-900' : '' }}">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $proc->pr_number }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                {{ $proc->procurement_type === 'perLot' ? 'Per Lot' : 'Per Item' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                {{ $proc->procurement_program_project }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $proc->date_receipt }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No procurements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination (fixed footer) -->
        <div class="p-2 border-t border-gray-200">
            <div class="flex justify-between items-center ">
                <div class="text-sm text-gray-600">
                    Showing {{ $procurements->firstItem() ?? 0 }} to {{ $procurements->lastItem() ?? 0 }} of
                    {{ $procurements->total() }} results
                </div>
                @if ($procurements->hasPages())
                    <nav role="navigation" aria-label="Pagination Navigation"
                        class="flex items-center justify-center space-x-2">
                        <button wire:click="previousPage('modalPage')" wire:loading.attr="disabled"
                            class="p-2 text-gray-600 hover:text-gray-900 {{ $procurements->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $procurements->onFirstPage() ? 'disabled' : '' }}>
                            <span class="text-xl">&lt;</span>
                        </button>

                        <span class="px-4 py-2 text-sm font-medium text-gray-700">
                            {{ $procurements->currentPage() }} of {{ $procurements->lastPage() }}
                        </span>

                        <button wire:click="nextPage('modalPage')" wire:loading.attr="disabled"
                            class="p-2 text-gray-600 hover:text-gray-900 {{ !$procurements->hasMorePages() ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ !$procurements->hasMorePages() ? 'disabled' : '' }}>
                            <span class="text-xl">&gt;</span>
                        </button>
                    </nav>
                @endif
                <button wire:click="selectProcurement" @class([
                    'inline-flex items-center gap-2 px-2 py-2 rounded-lg',
                    'text-white bg-blue-600 hover:bg-blue-700' => isset($selectedProcurement),
                    'text-gray-400 bg-gray-100 cursor-not-allowed' => !isset(
                        $selectedProcurement),
                ])
                    {{ !isset($selectedProcurement) ? 'disabled' : '' }}>
                    Select
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd"
                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm4.28 10.28a.75.75 0 0 0 0-1.06l-3-3a.75.75 0 1 0-1.06 1.06l1.72 1.72H8.25a.75.75 0 0 0 0 1.5h5.69l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</x-forms.modal>
