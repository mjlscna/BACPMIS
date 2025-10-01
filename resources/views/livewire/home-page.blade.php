<div
    class="m-4 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden
           dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-sm dark:shadow-neutral-400/50">
    <div
        class="sticky top-0 z-40 bg-white px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700 w-full">
        <div class="flex items-center gap-x-2">
            <div class="relative">
                <input type="text" wire:model.live="search" placeholder="Search Procurements..."
                    class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                <svg class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20" height="20"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M21 21l-4.35-4.35" />
                    <circle cx="10" cy="10" r="7" />
                </svg>
            </div>
        </div>
    </div>


    <!-- Table -->
    <div class="overflow-y-auto flex-1">
        <table class="table-fixed w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-200 dark:bg-neutral-900 sticky top-0 z-40">
                <tr>
                    <th class="w-10 px-2 py-2"></th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 w-[180px]">
                        PR Number
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 w-[100px]">
                        Type
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Program / Project
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Date Receipt
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                        PR Stage
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                @forelse($procurements as $proc)
                    <!-- Main Row -->
                    <tr wire:click="$set('selectedProcurement', {{ $proc->id }})"
                        class="hover:bg-gray-100 dark:hover:bg-neutral-800 cursor-pointer
            {{ isset($selectedProcurement) && $selectedProcurement === $proc->id ? 'bg-blue-50 dark:bg-blue-900' : '' }}">

                        <!-- Expand Button -->
                        <td class="px-2 py-2 text-center">
                            @if ($proc->procurement_type === 'perItem')
                                <button type="button"
                                    wire:click.stop="toggle('expandedProcurementId', {{ $proc->id }})"
                                    class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-neutral-700">
                                    @if ($expandedProcurementId === $proc->id)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </button>
                            @endif
                        </td>

                        <!-- PR Number -->
                        <td class="px-4 py-2 text-sm text-black dark:text-white">
                            {{ $proc->pr_number }}
                        </td>

                        <!-- Type -->
                        <td class="px-4 py-2 text-sm text-black dark:text-white">
                            {{ $proc->procurement_type === 'perLot' ? 'Per Lot' : 'Per Item' }}
                        </td>

                        <!-- Program / Project -->
                        <td class="px-4 py-2 text-sm text-black dark:text-white">
                            {{ $proc->procurement_program_project }}
                        </td>

                        <!-- Date Receipt -->
                        <td class="px-4 py-2 text-sm text-black dark:text-white">
                            {{ $proc->date_receipt }}
                        </td>

                        <!-- PR Stage -->
                        <td class="px-4 py-2 text-sm text-black dark:text-white">
                            @if ($proc->procurement_type === 'perLot')
                                {{-- Show latest perLot stage --}}
                                {{ $proc->prLotPrstages->sortByDesc('created_at')->first()?->procurementStage?->procurementstage ?? 'N/A' }}
                            @else
                                {{-- PerItem handled in expanded rows --}}
                            @endif
                        </td>
                    </tr>

                    <!-- Expanded Per Item Rows -->
                    @if ($proc->procurement_type === 'perItem' && $expandedProcurementId === $proc->id)
                        <tr>
                            <td colspan="6" class="pl-15 bg-white dark:bg-neutral-800">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-black dark:text-white">
                                                Description</th>
                                            <th class="px-4 py-2 text-left text-black dark:text-white">
                                                Amount</th>
                                            <th class="px-4 py-2 text-left text-black dark:text-white">
                                                PR Stage</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                        @foreach ($proc->pr_items as $item)
                                            <tr>
                                                <td
                                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                                    {{ $item->description }}</td>
                                                <td
                                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                                    {{ $item->amount }}</td>
                                                <td
                                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                                    {{ $item->stage_name ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-white">
                            No procurements found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center w-full py-4 -mt-2 relative">
        {{-- Left text --}}
        <div class="text-sm text-gray-500 pl-2">
            {{ $procurements->firstItem() }} to {{ $procurements->lastItem() }} of
            {{ $procurements->total() }} items
        </div>

        {{-- Center pagination --}}
        <div class="absolute left-1/2 transform -translate-x-1/2 mb-5">
            {{ $procurements->links('vendor.pagination.tailwind') }}
        </div>
    </div>


</div>
