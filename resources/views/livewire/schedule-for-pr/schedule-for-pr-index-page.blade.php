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

        <div class="overflow-x-auto">
            <table class="table-fixed w-full min-w-[1100px] divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-200 dark:bg-neutral-900 sticky top-0 z-20">
                    <tr>
                        {{-- Col 1: Actions (w-8 = 32px) --}}
                        <th class="px-2 py-2 sticky left-0 z-30 bg-gray-200 dark:bg-neutral-900 w-10"></th>

                        {{-- Col 2: IB Number (w-24 = 96px) | Offset by Col 1 --}}
                        <th
                            class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase sticky left-[32px] z-30 bg-gray-200 dark:bg-neutral-900 w-24">
                            IB Number
                        </th>

                        {{-- Col 3: Opening of Bids (w-28 = 112px) | Offset by Col 1 + 2 --}}
                        <th
                            class="px-1 py-1 text-left text-xs font-medium text-black dark:text-white uppercase sticky left-[128px] z-30 bg-gray-200 dark:bg-neutral-900 w-28">
                            Opening of Bids
                        </th>

                        {{-- Col 4: Name of Project | Offset by Col 1 + 2 + 3 --}}
                        <th
                            class="px-1 py-1 text-left text-xs font-medium text-black dark:text-white uppercase sticky left-[240px] z-30 bg-gray-200 dark:bg-neutral-900 w-lg">
                            Name of Project
                        </th>

                        {{-- Other header columns... --}}
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-28">
                            Framework
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-28">
                            Bidding Status
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-28">
                            Action Taken
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-32">
                            Next Bidding
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-32">
                            ABC Amount
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-32">
                            2%
                        </th>
                        <th class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase w-32">
                            5%
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                    @forelse ($schedules as $schedule)
                        <tr wire:key="schedule-{{ $schedule->id }}">
                            {{-- Col 1: Actions --}}
                            <td
                                class="px-2 py-2 text-center sticky left-0 bg-white text-black dark:text-white dark:bg-neutral-800">
                                <div x-data="{ open: false }" class="relative inline-block" x-ref="menuWrapper">
                                    <button @click="open = !open" @click.away="open = false"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-200 dark:hover:bg-neutral-700 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                        </svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open" x-transition @click.away="open = false"
                                            class="absolute z-[9999] bg-white border border-gray-200 rounded shadow-lg dark:bg-neutral-800 dark:border-neutral-700"
                                            x-ref="dropdown" x-init="$watch('open', value => {
                                                if (value) {
                                                    let rect = $refs.menuWrapper.getBoundingClientRect();
                                                    $refs.dropdown.style.top = (rect.top + window.scrollY) + 'px';
                                                    $refs.dropdown.style.left = (rect.right + 10 + window.scrollX) + 'px';
                                                }
                                            })">
                                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                                @can('view_schedule::for::procurement')
                                                    <li>
                                                        <a href="{{ $schedule->google_drive_link }}" target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-green-600">

                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                                            </svg>
                                                            View File
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('update_schedule::for::procurement')
                                                    <li>
                                                        <a href="{{ route('schedule-for-procurement.edit', $schedule->id) }}"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                            <x-heroicon-o-pencil class="w-4 h-4 text-amber-600" /> Edit
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </td>

                            {{-- Col 2: IB Number --}}
                            <td
                                class="px-1 py-1 text-center text-sm sticky left-[32px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-white">
                                {{ $schedule->ib_number }}
                            </td>

                            {{-- Col 3: Opening of Bids --}}
                            <td
                                class="px-1 py-1 text-center text-sm sticky left-[128px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-white">
                                {{ optional($schedule->opening_of_bids)->format('M d, Y') }}
                            </td>

                            {{-- Col 4: Name of Project --}}
                            <td
                                class="px-1 py-1 text-left text-sm sticky left-[240px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-white">
                                {{ $schedule->project_name }}
                            </td>

                            {{-- Other body cells... --}}
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-neutral-200">
                                @if ($schedule->is_framework)
                                    <x-heroicon-s-check-circle title="Yes"
                                        class="h-5 w-5 text-emerald-600 mx-auto" />
                                @else
                                    <x-heroicon-s-x-circle title="No" class="h-5 w-5 text-red-600 mx-auto" />
                                @endif
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-neutral-200">
                                {{ $schedule->biddingStatus?->name }}
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-neutral-200">
                                {{ $schedule->action_taken }}
                            </td>
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-neutral-200">
                                {{ optional($schedule->next_bidding_schedule)->format('M d, Y') }}
                            </td>
                            <td class="px-1 py-1 text-right text-sm text-black dark:text-neutral-200">
                                ₱{{ number_format($schedule->ABC, 2) }}
                            </td>
                            <td class="px-1 py-1 text-right text-sm text-black dark:text-neutral-200">
                                ₱{{ number_format($schedule->two_percent, 2) }}
                            </td>
                            <td class="px-1 py-1 text-right text-sm text-black dark:text-neutral-200">
                                ₱{{ number_format($schedule->five_percent, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-10 text-gray-500">
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
