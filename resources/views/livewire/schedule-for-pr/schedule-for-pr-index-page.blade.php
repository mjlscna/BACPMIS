<div x-data="{ showTypeModal: false }">
    <div
        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700 flex flex-col">

        <div
            class="sticky top-0 z-20 bg-white px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700 w-full">
            <div class="flex items-center gap-x-2">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Search by IB No. or Project Name..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                    <svg class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20" height="20"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 21l-4.35-4.35" />
                        <circle cx="10" cy="10" r="7" />
                    </svg>
                </div>
                @can('create_schedule::for::procurement')
                    <button type="button" @click="showTypeModal = true"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Schedule
                    </button>
                @endcan
            </div>
        </div>

        <div class="overflow-auto flex-1">
            <table class="table-fixed w-full min-w-[1100px] divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-200 dark:bg-neutral-900 sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-2 sticky left-0 z-30 bg-gray-200 dark:bg-neutral-900 w-8"></th>
                        <th
                            class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase sticky left-[32px] z-20 bg-gray-200 dark:bg-neutral-900 w-28">
                            IB Number
                        </th>
                        <th
                            class="px-1 py-1 text-left text-xs font-medium text-black dark:text-white uppercase sticky left-[144px] z-10 bg-gray-200 dark:bg-neutral-900 w-md">
                            Project Name
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-28">
                            Opening of Bids
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-28">
                            Status
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-32">
                            Next Bidding
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-28">
                            Framework
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-24">
                            Items/Lots
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-24">
                            PR Count
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white w-32">
                            ABC Amount
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td
                                class="px-2 py-2 text-center sticky left-0 bg-white text-black dark:text-white dark:bg-neutral-800">
                            </td>
                            <td
                                class="px-1 py-1 text-center text-sm sticky left-[32px] z-20 bg-white dark:bg-neutral-800 text-black dark:text-white">
                                {{ $schedule->ib_number }}
                            </td>
                            <td
                                class="px-1 py-1 text-left text-sm sticky left-[144px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-white">
                                {{ $schedule->project_name }}
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                {{ $schedule->opening_of_bids->format('M d, Y') }}
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                @if ($schedule->biddingStatus)
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                                        style="background-color: {{ $schedule->biddingStatus->color ?? '#9ca3af' }}20; color: {{ $schedule->biddingStatus->color ?? '#9ca3af' }};">
                                        {{ $schedule->biddingStatus->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                {{ $schedule->next_bidding_schedule?->format('M d, Y') }}
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-neutral-200">
                                @if ($schedule->is_framework)
                                    <x-heroicon-s-check-circle title="Yes"
                                        class="h-5 w-5 text-emerald-600 mx-auto" />
                                @else
                                    <x-heroicon-s-x-circle title="No" class="h-5 w-5 text-red-600 mx-auto" />
                                @endif
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                {{ $schedule->no_items_lot }}
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                {{ $schedule->pr_count }}
                            </td>
                            <td class="px-1 py-1 pr-4 text-right text-sm text-black dark:text-white relative">
                                <span>{{ number_format($schedule->approved_budget_contract ?? 0, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-10 text-gray-500">
                                No bidding schedules found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col items-center w-full p-2 border-t border-gray-200 dark:border-neutral-700">

            <div class="text-xs text-gray-500">
                {{ $schedules->firstItem() }} to {{ $schedules->lastItem() }} of
                {{ $schedules->total() }} items
            </div>

            <div>
                {{ $schedules->links('vendor.pagination.tailwind') }}
            </div>

        </div>
        <div @keydown.escape.window="showTypeModal = false" x-show="showTypeModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.outside="showTypeModal = false"
                class="bg-white shadow-xl w-full max-w-md rounded-2xl dark:bg-neutral-800">
                <div
                    class="flex justify-between items-center px-4 py-2 bg-emerald-600 text-white font-semibold dark:bg-neutral-900 rounded-t-2xl">
                    <h2 class="text-lg font-semibold">Select Procurement Type</h2>
                    <button @click="showTypeModal = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-white/70 hover:text-white transition">✕</button>
                </div>
                <div class="p-6 text-center">
                    <p class="text-gray-700 dark:text-neutral-300">Please choose the type of procurement you are
                        creating schedule for.</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-neutral-800/50 rounded-b-2xl flex justify-center gap-x-4">
                    <a href="{{ route('schedule-for-procurement.create', ['type' => 'perLot']) }}"
                        class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">Per
                        Lot</a>
                    <a href="{{ route('schedule-for-procurement.create', ['type' => 'perItem']) }}"
                        class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-sky-600 text-white hover:bg-sky-700">Per
                        Item</a>
                </div>
            </div>
        </div>
    </div>
</div>
