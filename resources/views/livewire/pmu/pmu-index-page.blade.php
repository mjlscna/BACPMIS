<div>
    <div wire:poll.10s>
        {{-- ═══ Pending Receipt ════════════════════════════════════════════════════ --}}
        @if ($pendingItems->total() > 0)
            <div x-data="{ open: false }"
                class="bg-white border border-orange-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-orange-800/50 flex flex-col mb-6">

                <!-- Pending Card Header -->
                <div @click="open = !open" role="button"
                    class="flex items-center justify-between px-6 py-3 border-b border-orange-200 dark:border-orange-800/50 bg-orange-50 dark:bg-orange-950/20 cursor-pointer select-none">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/40">
                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-orange-800 dark:text-orange-300">Pending Receipt</h3>
                        <span
                            class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold bg-orange-200 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                            {{ $pendingItems->total() }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        @can('update_pmu')
                            @if (count($selectedNoaNumbers) > 0)
                                <div class="flex items-center gap-2" @click.stop>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        <span
                                            class="font-semibold text-emerald-600 dark:text-emerald-400">{{ count($selectedNoaNumbers) }}</span>
                                        selected
                                    </span>
                                    <button wire:click="clearSelection"
                                        class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors">
                                        Clear
                                    </button>
                                    <button wire:click="openBulkReceiveModal"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Receive
                                    </button>
                                </div>
                            @endif
                        @endcan
                        <svg class="w-4 h-4 text-orange-500 dark:text-orange-400 transition-transform duration-200"
                            :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>

                <!-- Pending Table -->
                <div x-show="open" x-transition class="overflow-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead
                            class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-neutral-900 dark:to-neutral-800">
                            <tr>
                                <th class="px-3 py-2 bg-gray-100 dark:bg-neutral-900 w-10">
                                    <input type="checkbox"
                                        @if ($pendingItems->total() > 0) x-data x-on:click="
                                            let boxes = document.querySelectorAll('[data-noa-check]');
                                            let anyUnchecked = Array.from(boxes).some(b => !b.checked);
                                            boxes.forEach(b => {
                                                b.checked = anyUnchecked;
                                                b.dispatchEvent(new Event('change'));
                                            });
                                        " @endif
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-neutral-700 dark:border-neutral-500 cursor-pointer"
                                        title="Select / deselect all visible" />
                                </th>
                                <th class="px-2 py-1 bg-gray-100 dark:bg-neutral-900 w-12"></th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                    Notice of Award Number
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                    Date Forwarded From BAC
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                    Notice of Award Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                            @forelse ($pendingItems as $group)
                                <!-- Pending Row -->
                                <tr
                                    class="transition-colors border-l-4 border-l-orange-400 {{ in_array($group->notice_of_award_number, $selectedNoaNumbers) ? 'bg-emerald-50 dark:bg-emerald-950/20 ring-1 ring-inset ring-emerald-300 dark:ring-emerald-700' : 'bg-orange-50 hover:bg-orange-100 dark:bg-orange-950/20 dark:hover:bg-orange-950/30' }}">
                                    @can('update_pmu')
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <input type="checkbox" data-noa-check wire:model.live="selectedNoaNumbers"
                                                value="{{ $group->notice_of_award_number }}"
                                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-neutral-700 dark:border-neutral-500 cursor-pointer" />
                                        </td>
                                    @endcan
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <button
                                            wire:click="toggle('expandedNoaNumber', '{{ $group->notice_of_award_number }}')"
                                            class="flex items-center gap-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                            <svg class="w-5 h-5 transition-transform {{ $expandedNoaNumber === $group->notice_of_award_number ? 'rotate-90' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                            <span
                                                class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 min-w-[20px]">
                                                {{ $group->procurement_count }}
                                            </span>
                                        </button>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ $group->notice_of_award_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $group->date_forwarded ? \Carbon\Carbon::parse($group->date_forwarded)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $group->notice_of_award ? \Carbon\Carbon::parse($group->notice_of_award)->format('M d, Y') : '—' }}
                                    </td>
                                </tr>

                                @if ($expandedNoaNumber === $group->notice_of_award_number && $expandedPaginator)
                                    <tr class="bg-gray-50 dark:bg-neutral-900">
                                        <td colspan="99" class="px-4 py-3">
                                            <div
                                                class="overflow-x-auto overflow-y-auto max-h-[55vh] rounded-lg border border-gray-200 dark:border-neutral-700">
                                                @include('livewire.pmu.partials.expanded-table')
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="99" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mb-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">All NOAs
                                                have
                                                been
                                                received</p>
                                            <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">No pending items to
                                                process</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div x-show="open" x-transition>
                    @if ($pendingItems->hasPages() || $pendingItems->total() > 0)
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full px-4 py-3 border-t border-orange-200 dark:border-orange-800/50 gap-3">
                            <div class="flex items-center gap-x-2">
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Show</label>
                                <select wire:model.live="pendingPerPage"
                                    class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all duration-200 dark:bg-neutral-700 dark:text-white dark:border-neutral-600">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span class="text-xs text-gray-500 dark:text-gray-400">per page</span>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-2 flex-1">
                                <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Showing
                                    <span
                                        class="text-orange-600 dark:text-orange-400 font-semibold">{{ $pendingItems->firstItem() ?? 0 }}</span>
                                    to
                                    <span
                                        class="text-orange-600 dark:text-orange-400 font-semibold">{{ $pendingItems->lastItem() ?? 0 }}</span>
                                    of
                                    <span
                                        class="text-orange-600 dark:text-orange-400 font-semibold">{{ $pendingItems->total() }}</span>
                                    items
                                </div>
                                @if ($pendingItems->hasPages())
                                    <div class="flex items-center gap-1">
                                        <button wire:click="setPendingPage({{ $pendingItems->currentPage() - 1 }})"
                                            @disabled($pendingItems->onFirstPage())
                                            class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                            Previous
                                        </button>
                                        @for ($p = 1; $p <= $pendingItems->lastPage(); $p++)
                                            <button wire:click="setPendingPage({{ $p }})"
                                                class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors duration-150 {{ $p === $pendingItems->currentPage() ? 'bg-orange-500 text-white border-orange-500 hover:bg-orange-600' : 'border-gray-300 hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white' }}">
                                                {{ $p }}
                                            </button>
                                        @endfor
                                        <button wire:click="setPendingPage({{ $pendingItems->currentPage() + 1 }})"
                                            @disabled(!$pendingItems->hasMorePages())
                                            class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                            Next
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ═══ Received NOAs ════════════════════════════════════ --}}
        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700 flex flex-col mb-6">

            <!-- Received Header: Search + Filter Toggle -->
            <div x-data="{ showFilters: false }"
                class="sticky top-0 z-10 bg-white dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700">

                <!-- Search Row -->
                <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                    <div class="relative w-72">
                        <input type="text" wire:model.live="search"
                            placeholder="Search NOA, PR, PO / Contract numbers..."
                            class="w-full px-4 py-2 pl-9 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all" />
                        <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Filter Toggle Button -->
                    @php
                        $activeFilterCount =
                            (int) ($poStatusFilter !== '') +
                            (int) ($poIssuanceFilter !== '') +
                            (int) ($sortBy !== 'date_received' || $sortDir !== 'desc');
                    @endphp
                    <button @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border transition-all duration-200"
                        :class="showFilters
                            ?
                            'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-300 dark:border-emerald-600 text-emerald-700 dark:text-emerald-300' :
                            'bg-gray-100 dark:bg-neutral-700 border-gray-200 dark:border-neutral-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-neutral-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filters</span>
                        @if ($activeFilterCount > 0)
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold bg-emerald-600 text-white">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>
                </div>

                <!-- Expandable Filter Panel -->
                <div x-show="showFilters" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/50 px-4 py-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

                        <!-- Sort By -->
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Sort
                                By</label>
                            <select wire:model.live="sortBy"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                <option value="date_received">Date Received</option>
                                <option value="notice_of_award_number">NOA Number</option>
                            </select>
                        </div>

                        <!-- Sort Direction -->
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Sort
                                Direction</label>
                            <select wire:model.live="sortDir"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                <option value="desc">Newest First</option>
                                <option value="asc">Oldest First</option>
                            </select>
                        </div>

                        <!-- PO Status -->
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">PO
                                Status</label>
                            <select wire:model.live="poStatusFilter"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                <option value="">All</option>
                                <option value="pending_entry">Pending Entry</option>
                                <option value="po_prep">PO Preparation</option>
                                <option value="usec">For Approval of HOPE</option>
                                <option value="return_to_bac">Return to BAC</option>
                                <option value="for_end_user_compliance">For End-User Compliance</option>
                                <option value="forwarded_to_supply">Forwarded to Supply</option>
                            </select>
                        </div>

                        <!-- PO Issuance -->
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">PO
                                Issuance</label>
                            <select wire:model.live="poIssuanceFilter"
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                <option value="">All</option>
                                <option value="on_track">On Track</option>
                                <option value="due_soon">Due Soon</option>
                                <option value="overdue">Overdue</option>
                                <option value="exceeded">Exceeded Deadline</option>
                            </select>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    @if ($activeFilterCount > 0 || $search !== '')
                        <div class="mt-3 flex justify-end">
                            <button wire:click="clearReceivedFilters"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear Filters
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Received Table -->
            <div class="overflow-auto flex-1">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead
                        class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-neutral-900 dark:to-neutral-800">
                        <tr>
                            <th class="px-2 py-1 bg-gray-100 dark:bg-neutral-900 w-12"></th>
                            <th class="px-2 py-1 bg-gray-100 dark:bg-neutral-900 w-12"></th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Notice of Award Number</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Date Forwarded From BAC</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Date Received</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Notice of Award Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                PO Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                PO Issuance</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                        @forelse ($receivedItems as $group)
                            <!-- Received Row -->
                            <tr
                                class="transition-colors bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700">
                                <!-- Toggle -->
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <button
                                        wire:click="toggle('expandedNoaNumber', '{{ $group->notice_of_award_number }}')"
                                        class="flex items-center gap-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                        <svg class="w-5 h-5 transition-transform {{ $expandedNoaNumber === $group->notice_of_award_number ? 'rotate-90' : '' }}"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                        <span
                                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 min-w-[20px]">
                                            {{ $poIssuanceCounts->get($group->notice_of_award_number)->total_count ?? $group->procurement_count }}
                                        </span>
                                    </button>
                                </td>
                                <!-- Actions Dropdown -->
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div x-data="{ open: false }" class="relative inline-block" x-ref="menuWrapper">
                                        <button @click="open = !open" @click.away="open = false"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:border-emerald-300 dark:hover:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition-all duration-200 shadow-sm hover:shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                class="size-5 text-gray-600 dark:text-gray-300">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                            </svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                @click.away="open = false"
                                                class="absolute z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl dark:bg-neutral-800 dark:border-neutral-700 min-w-[180px] overflow-hidden"
                                                x-ref="dropdown" x-init="$watch('open', value => {
                                                    if (value) {
                                                        let rect = $refs.menuWrapper.getBoundingClientRect();
                                                        $refs.dropdown.style.top = (rect.top + window.scrollY) + 'px';
                                                        $refs.dropdown.style.left = (rect.right + 10 + window.scrollX) + 'px';
                                                    }
                                                })">
                                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                                    @can('view_pmu')
                                                        <li>
                                                            <button
                                                                wire:click="openViewModal('{{ $group->notice_of_award_number }}')"
                                                                class="w-full flex items-center gap-2.5 text-left px-4 py-2.5 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 dark:hover:from-blue-900/30 dark:hover:to-blue-800/30 text-blue-600 dark:text-blue-400 transition-all duration-150 group/item">
                                                                <x-heroicon-o-eye
                                                                    class="w-4 h-4 group-hover/item:scale-110 transition-transform" />
                                                                <span class="font-medium">View Details</span>
                                                            </button>
                                                        </li>
                                                    @endcan
                                                    @can('update_pmu')
                                                        <li>
                                                            <a href="{{ route('pmu.edit', $group->notice_of_award_number) }}"
                                                                class="w-full flex items-center gap-2.5 text-left px-4 py-2.5 hover:bg-gradient-to-r hover:from-amber-50 hover:to-amber-100 dark:hover:from-amber-900/30 dark:hover:to-amber-800/30 text-amber-600 dark:text-amber-400 transition-all duration-150 group/item">
                                                                <x-heroicon-o-pencil-square
                                                                    class="w-4 h-4 group-hover/item:scale-110 transition-transform" />
                                                                <span class="font-medium">Update</span>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    {{-- @can('edit_pmu')
                                                    <li>
                                                        <button
                                                            wire:click="openReceiveModal('{{ $group->notice_of_award_number }}')"
                                                            class="w-full flex items-center gap-2.5 text-left px-4 py-2.5 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-emerald-100 dark:hover:from-emerald-900/30 dark:hover:to-emerald-800/30 text-emerald-600 dark:text-emerald-400 transition-all duration-150 group/item">
                                                            <x-heroicon-o-calendar-days
                                                                class="w-4 h-4 group-hover/item:scale-110 transition-transform" />
                                                            <span class="font-medium">Edit Received</span>
                                                        </button>
                                                    </li>
                                                @endcan --}}
                                                </ul>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <!-- NOA Number -->
                                <td
                                    class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $group->notice_of_award_number }}
                                </td>
                                <!-- Date Forwarded -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $group->date_forwarded ? \Carbon\Carbon::parse($group->date_forwarded)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : '—' }}
                                </td>
                                <!-- Date Received -->
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                    {{ \Carbon\Carbon::parse($group->date_received, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y h:i A') }}
                                </td>
                                <!-- Notice of Award Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $group->notice_of_award ? \Carbon\Carbon::parse($group->notice_of_award)->format('M d, Y') : '—' }}
                                </td>
                                <!-- PO Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $ic = $poIssuanceCounts->get($group->notice_of_award_number);
                                        $poTotal = (int) ($ic->total_count ?? 0);
                                        $readyToForward = (int) ($ic->ready_to_forward_count ?? 0);
                                        $poPrep = (int) ($ic->po_prep_count ?? 0);
                                        $usecCount = (int) ($ic->usec_count ?? 0);
                                        $rtoBAC = (int) ($ic->return_to_bac_count ?? 0);
                                        $endUser = (int) ($ic->end_user_count ?? 0);
                                        $forwardedToSupply = (int) ($ic->forwarded_to_supply_count ?? 0);
                                    @endphp
                                    <div class="flex flex-wrap gap-1">
                                        @if ($poTotal === 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-gray-400"
                                                title="No PO records entered yet">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                                                Not Started
                                            </span>
                                        @else
                                            @if ($forwardedToSupply > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300"
                                                    title="{{ $forwardedToSupply }} of {{ $poTotal }} item(s) forwarded to Supply">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                                                    {{ $forwardedToSupply }}/{{ $poTotal }} Forwarded to Supply
                                                </span>
                                            @endif
                                            @if ($readyToForward > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
                                                    title="{{ $readyToForward }} of {{ $poTotal }} item(s) have all required fields complete — Ready to Forward">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    {{ $readyToForward }}/{{ $poTotal }} Ready to Forward
                                                </span>
                                            @endif
                                            @if ($rtoBAC > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300"
                                                    title="{{ $rtoBAC }} of {{ $poTotal }} item(s) flagged as Return to BAC">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    {{ $rtoBAC }}/{{ $poTotal }} Return to BAC
                                                </span>
                                            @endif
                                            @if ($endUser > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300"
                                                    title="{{ $endUser }} of {{ $poTotal }} item(s) flagged as For End-User Compliance">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                    {{ $endUser }}/{{ $poTotal }} For End-User Compliance
                                                </span>
                                            @endif
                                            @if ($usecCount > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                                                    title="{{ $usecCount }} of {{ $poTotal }} item(s) with Contract Amount filled — For Approval of HOPE">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                    {{ $usecCount }}/{{ $poTotal }} For Approval of HOPE
                                                </span>
                                            @endif
                                            @if ($poPrep > 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                                                    title="{{ $poPrep }} of {{ $poTotal }} item(s) have PO Date and PO/Contract No. filled — PO Preparation">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    {{ $poPrep }}/{{ $poTotal }} PO Preparation
                                                </span>
                                            @endif
                                            @if (
                                                $readyToForward === 0 &&
                                                    $usecCount === 0 &&
                                                    $poPrep === 0 &&
                                                    $rtoBAC === 0 &&
                                                    $endUser === 0 &&
                                                    $forwardedToSupply === 0)
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-gray-400"
                                                    title="No PO data recorded yet">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                    Pending Entry
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <!-- PO Issuance -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $wc = $warningCounts->get($group->notice_of_award_number);
                                        $exceeded = (int) ($wc->exceeded_count ?? 0);
                                        $overdue = (int) ($wc->overdue_count ?? 0);
                                        $soon = (int) ($wc->soon_count ?? 0);
                                    @endphp
                                    <div class="flex flex-wrap gap-1">
                                        @if ($exceeded > 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300"
                                                title="{{ $exceeded }} item(s) with PO Date exceeding the deadline">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $exceeded }} Exceeded
                                            </span>
                                        @endif
                                        @if ($overdue > 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300"
                                                title="{{ $overdue }} item(s) past the PO Date Deadline with no PO Date recorded">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $overdue }} Overdue
                                            </span>
                                        @endif
                                        @if ($soon > 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                                                title="{{ $soon }} item(s) with deadline within 3 days">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $soon }} Due Soon
                                            </span>
                                        @endif
                                        @if ($exceeded === 0 && $overdue === 0 && $soon === 0)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                On Track
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <!-- Remarks -->
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 max-w-[12rem]">
                                    @if ($group->received_remarks)
                                        <span class="block truncate" title="{{ $group->received_remarks }}">
                                            {{ $group->received_remarks }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                            </tr>

                            @if ($expandedNoaNumber === $group->notice_of_award_number && $expandedPaginator)
                                <tr class="bg-gray-50 dark:bg-neutral-900">
                                    <td colspan="99" class="px-4 py-3">
                                        <div
                                            class="overflow-x-auto overflow-y-auto max-h-[55vh] rounded-lg border border-gray-200 dark:border-neutral-700">
                                            @include('livewire.pmu.partials.expanded-table')
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="99" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">No received NOAs yet</p>
                                        <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Mark pending NOAs as
                                            received to see them here</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($receivedItems->hasPages() || $receivedItems->total() > 0)
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full px-4 py-3 border-t border-gray-200 dark:border-neutral-700 gap-3 bg-gradient-to-r from-gray-50 to-white dark:from-neutral-900 dark:to-neutral-800">
                    <div class="flex items-center gap-x-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Show</label>
                        <select wire:model.live="receivedPerPage"
                            class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 dark:bg-neutral-700 dark:text-white dark:border-neutral-600">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-xs text-gray-500 dark:text-gray-400">per page</span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-2 flex-1">
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                            Showing
                            <span
                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $receivedItems->firstItem() ?? 0 }}</span>
                            to
                            <span
                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $receivedItems->lastItem() ?? 0 }}</span>
                            of
                            <span
                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $receivedItems->total() }}</span>
                            items
                        </div>
                        @if ($receivedItems->hasPages())
                            <div class="flex items-center gap-1">
                                <button wire:click="setReceivedPage({{ $receivedItems->currentPage() - 1 }})"
                                    @disabled($receivedItems->onFirstPage())
                                    class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Previous
                                </button>
                                @for ($p = 1; $p <= $receivedItems->lastPage(); $p++)
                                    <button wire:click="setReceivedPage({{ $p }})"
                                        class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors duration-150 {{ $p === $receivedItems->currentPage() ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : 'border-gray-300 hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white' }}">
                                        {{ $p }}
                                    </button>
                                @endfor
                                <button wire:click="setReceivedPage({{ $receivedItems->currentPage() + 1 }})"
                                    @disabled(!$receivedItems->hasMorePages())
                                    class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Next
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ MODALS (Outside polling) ════════════════════════════════════ --}}

    {{-- View Details Modal --}}
    @if ($showViewModal)
        <div wire:ignore>
            <x-forms.modal title="PMU Details — {{ $viewNoaNumber }}" size="max-w-[98vw]"
                closeMethod="closeViewModal" model="showViewModal">
                <div class="space-y-4 p-4">

                    {{-- NOA Header Box --}}
                    <div
                        class="bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl shadow-sm overflow-hidden">
                        <div class="h-1 bg-emerald-600"></div>
                        <div class="flex flex-wrap items-stretch">

                            {{-- NOA Number — emerald panel --}}
                            <div class="bg-emerald-600 dark:bg-emerald-700 px-5 py-4 flex flex-col justify-center">
                                <p class="text-xs font-medium text-emerald-100 mb-0.5">Notice of Award No.</p>
                                <p class="text-lg font-bold text-white">{{ $viewNoaNumber ?? '—' }}</p>
                            </div>

                            {{-- Divider --}}
                            <div class="w-px bg-gray-200 dark:bg-neutral-600 hidden sm:block"></div>

                            {{-- Date Forwarded --}}
                            <div class="px-5 py-4 flex flex-col justify-center">
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Date Forwarded</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $viewPmuRecord?->date_forwarded ? \Carbon\Carbon::parse($viewPmuRecord->date_forwarded)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : '—' }}
                                </p>
                            </div>

                            {{-- Divider --}}
                            <div class="w-px bg-gray-200 dark:bg-neutral-600 hidden sm:block"></div>

                            {{-- Notice of Award Date --}}
                            <div class="px-5 py-4 flex flex-col justify-center">
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Notice of Award Date</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $viewPostProcurement?->notice_of_award ? \Carbon\Carbon::parse($viewPostProcurement->notice_of_award)->format('M d, Y') : '—' }}
                                </p>
                            </div>

                            {{-- Spacer --}}
                            <div class="flex-1"></div>

                        </div>
                    </div>

                    {{-- Linked PRs / Items --}}
                    <div
                        class="bg-white rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700 overflow-hidden">

                        {{-- Section header --}}
                        <div
                            class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-neutral-600 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-neutral-800 dark:to-neutral-700">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Linked PRs /
                                    Items</span>
                                @php $totalLinked = $modalPaginator?->total() ?? 0; @endphp
                                <span
                                    class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ $totalLinked }}
                                </span>
                            </div>

                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-600">
                                <thead
                                    class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-neutral-900 dark:to-neutral-800">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            PR Number</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                            Title / Description</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            ABC / Amount</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            Awarded Amt</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                            Supplier</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            Date Receipt of Supplier (NOA)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            PO Date Deadline</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            PO Date</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            PO / Contract No.</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            Contract Amt</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            Signing Date</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            NTP Date</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider whitespace-nowrap">
                                            NTP Link</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                            Remarks</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                    @forelse ($modalPaginator ?? [] as $row)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @can('view_procurement')
                                                    <a href="{{ route('procurements.view', ['procurement' => $row->procID]) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-md font-mono text-xs text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 hover:border-emerald-400 transition-colors">
                                                        {{ $row->pr_number }}
                                                    </a>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-md font-mono text-xs text-emerald-700 dark:text-emerald-300">
                                                        {{ $row->pr_number }}
                                                    </span>
                                                @endcan
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                <div class="break-words whitespace-normal max-w-xs">
                                                    {{ $row->description }}</div>
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                                ₱ {{ number_format($row->abc, 2) }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $row->awarded_amount ? '₱ ' . number_format($row->awarded_amount, 2) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="break-words whitespace-normal max-w-[10rem]">
                                                    {{ $row->supplier_name ?? '—' }}</div>
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $row->date_receipt_of_supplier_noa ? \Carbon\Carbon::parse($row->date_receipt_of_supplier_noa)->format('M d, Y') : '—' }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                @php
                                                    $deadlineWarning = null;
                                                    if ($row->po_date_deadline) {
                                                        $deadline = \Carbon\Carbon::parse($row->po_date_deadline);
                                                        $today = \Carbon\Carbon::today();
                                                        $daysUntil = $today->diffInDays($deadline, false);
                                                        if (
                                                            $row->po_date &&
                                                            \Carbon\Carbon::parse($row->po_date)->gt($deadline)
                                                        ) {
                                                            $deadlineWarning = 'exceeded';
                                                        } elseif ($daysUntil < 0 && !$row->po_date) {
                                                            $deadlineWarning = 'overdue';
                                                        } elseif (
                                                            $daysUntil >= 0 &&
                                                            $daysUntil <= 3 &&
                                                            !$row->po_date
                                                        ) {
                                                            $deadlineWarning = 'soon';
                                                        }
                                                    }
                                                @endphp
                                                <div class="flex flex-col gap-1">
                                                    <span>{{ $row->po_date_deadline ? \Carbon\Carbon::parse($row->po_date_deadline)->format('M d, Y') : '—' }}</span>
                                                    @if ($deadlineWarning === 'exceeded')
                                                        <span
                                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                                            <svg class="w-3 h-3" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Exceeded
                                                        </span>
                                                    @elseif ($deadlineWarning === 'overdue')
                                                        <span
                                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                                            <svg class="w-3 h-3" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Overdue
                                                        </span>
                                                    @elseif ($deadlineWarning === 'soon')
                                                        <span
                                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                                            <svg class="w-3 h-3" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Due Soon
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $row->po_date ? \Carbon\Carbon::parse($row->po_date)->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                @if ($row->po_contract_number)
                                                    @if ($row->po_contract_number_link)
                                                        <a href="{{ $row->po_contract_number_link }}" target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex items-center gap-1 font-medium text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 underline underline-offset-2 transition-colors">
                                                            {{ $row->po_contract_number }}
                                                        </a>
                                                    @else
                                                        <span
                                                            class="font-medium text-gray-900 dark:text-white">{{ $row->po_contract_number }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $row->contract_amount ? '₱ ' . number_format($row->contract_amount, 2) : '—' }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $row->contract_signing_date ? \Carbon\Carbon::parse($row->contract_signing_date)->format('M d, Y') : '—' }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $row->notice_to_proceed_date ? \Carbon\Carbon::parse($row->notice_to_proceed_date)->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                @if ($row->ntp_link)
                                                    <a href="{{ $row->ntp_link }}" target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex items-center gap-1 font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 underline underline-offset-2 transition-colors">
                                                        View NTP
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                @if ($row->remarks)
                                                    <span title="{{ $row->remarks }}"
                                                        class="cursor-help">{{ \Illuminate\Support\Str::limit($row->remarks, 40) }}</span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="14"
                                                class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No linked PRs or items found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- Modal pagination --}}
                            @if ($modalPaginator && ($modalPaginator->hasPages() || $modalPaginator->total() > 0))
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full p-4 border-t border-gray-200 dark:border-neutral-700 gap-3 bg-gradient-to-r from-gray-50 to-white dark:from-neutral-900 dark:to-neutral-800">

                                    <!-- Left: Per-page selector -->
                                    <div class="flex items-center gap-x-2">
                                        <label for="modalPerPage"
                                            class="text-xs font-medium text-gray-600 dark:text-gray-300">Show</label>
                                        <select id="modalPerPage" wire:model.live="modalPerPage"
                                            class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 dark:bg-neutral-700 dark:text-white dark:border-neutral-600">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">per page</span>
                                    </div>

                                    <!-- Center: Summary + Pagination -->
                                    <div class="flex flex-col items-center justify-center gap-3 flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                            Showing
                                            <span
                                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $modalPaginator->firstItem() ?? 0 }}</span>
                                            to
                                            <span
                                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $modalPaginator->lastItem() ?? 0 }}</span>
                                            of
                                            <span
                                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $modalPaginator->total() }}</span>
                                            items
                                        </div>

                                        @if ($modalPaginator->hasPages())
                                            <div class="flex items-center gap-1">
                                                <button
                                                    wire:click="setModalPage({{ $modalPaginator->currentPage() - 1 }})"
                                                    @disabled($modalPaginator->onFirstPage())
                                                    class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                                    Previous
                                                </button>
                                                @for ($p = 1; $p <= $modalPaginator->lastPage(); $p++)
                                                    <button wire:click="setModalPage({{ $p }})"
                                                        class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors duration-150 {{ $p === $modalPaginator->currentPage() ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : 'border-gray-300 hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white' }}">
                                                        {{ $p }}
                                                    </button>
                                                @endfor
                                                <button
                                                    wire:click="setModalPage({{ $modalPaginator->currentPage() + 1 }})"
                                                    @disabled(!$modalPaginator->hasMorePages())
                                                    class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                                    Next
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </x-forms.modal>
        </div>
    @endif

    {{-- Receive Modal --}}
    @if ($showReceiveModal)
        <div wire:ignore>
            <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm"
                wire:click.self="closeReceiveModal">
                <div
                    class="bg-white dark:bg-neutral-800 rounded-xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md mx-4">
                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between px-5 py-4 border-b border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/20 rounded-t-xl">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Mark as Received</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">NOA: <span
                                        class="font-medium text-emerald-600 dark:text-emerald-400">{{ $receivingNoaNumber }}</span>
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeReceiveModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-5 py-4 space-y-4">
                        {{-- Date Received --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date Received <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" wire:model="receiveDate"
                                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                                   @error('receiveDate') border-red-400 dark:border-red-500 @else border-gray-300 @enderror" />
                            @error('receiveDate')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remarks --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Remarks <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea wire:model="receiveRemarks" rows="3" placeholder="Add any notes about document receipt..."
                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-neutral-700 dark:text-white resize-none"></textarea>
                            @error('receiveRemarks')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                        <button wire:click="closeReceiveModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="confirmReceive" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 rounded-lg transition-colors">
                            <svg wire:loading wire:target="confirmReceive" class="w-4 h-4 animate-spin"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg wire:loading.remove wire:target="confirmReceive" class="w-4 h-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Confirm Received
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Receive Modal --}}
    <div wire:ignore>
        <x-forms.modal :model="'showBulkReceiveModal'" :closeMethod="'closeBulkReceiveModal'" :title="'Mark as Received'" size="max-w-lg">
            <div class="px-4 py-3">

                {{-- Summary --}}
                <div
                    class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                    <p class="text-sm text-emerald-900 dark:text-emerald-100">
                        <span class="font-semibold">{{ count($selectedNoaNumbers) }}</span> NOA(s) selected for
                        receipt.
                    </p>
                </div>

                {{-- Selected NOA List --}}
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Selected NOA Numbers</p>
                    <div
                        class="max-h-36 overflow-y-auto rounded-lg border border-gray-200 dark:border-neutral-600 bg-gray-50 dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                        @foreach ($selectedNoaNumbers as $noa)
                            <div class="flex items-center justify-between px-3 py-2">
                                <span
                                    class="text-sm font-medium text-gray-900 dark:text-white">{{ $noa }}</span>
                                <button wire:click="toggleNoaSelection('{{ $noa }}')"
                                    class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                    title="Remove from selection">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Date Received --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Date Received <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" wire:model="bulkReceiveDate"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                           @error('bulkReceiveDate') border-red-400 dark:border-red-500 @else border-gray-300 @enderror" />
                    @error('bulkReceiveDate')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">This date will be applied to all selected
                        NOA
                        numbers.</p>
                </div>

                {{-- Remarks --}}
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Remarks <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea wire:model="bulkReceiveRemarks" rows="3" placeholder="Add notes about document receipt..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-neutral-700 dark:text-white resize-none"></textarea>
                    @error('bulkReceiveRemarks')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Footer --}}
                <div
                    class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 flex justify-between items-center gap-3 border-t border-gray-200 dark:border-neutral-600 -mx-4 -mb-3 mt-4">
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ count($selectedNoaNumbers) }} record(s) will be updated
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeBulkReceiveModal"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-neutral-800 dark:text-gray-300 dark:border-neutral-600 dark:hover:bg-neutral-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </button>
                        <button type="button" wire:click="confirmBulkReceive" wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-60">
                            <svg wire:loading wire:target="confirmBulkReceive" class="w-4 h-4 animate-spin"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg wire:loading.remove wire:target="confirmBulkReceive" class="w-4 h-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Confirm Receive
                        </button>
                    </div>
                </div>

            </div>
        </x-forms.modal>
    </div>

</div>
