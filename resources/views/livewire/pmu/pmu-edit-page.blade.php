<div class="space-y-6 p-2 pb-[5rem]">

    {{-- NOA Header Box --}}
    <div
        class="bg-white dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-xl shadow-sm overflow-hidden">

        {{-- Emerald top line --}}
        <div class="h-1 bg-emerald-600"></div>

        <div class="flex flex-wrap items-stretch">

            {{-- NOA Number — emerald panel --}}
            <div class="bg-emerald-600 dark:bg-emerald-700 px-5 py-4 flex flex-col justify-center">
                <p class="text-xs font-medium text-emerald-100 mb-0.5">Notice of Award No.</p>
                <p class="text-lg font-bold text-white">{{ $noticeOfAwardNumber ?? '—' }}</p>
            </div>

            {{-- Divider --}}
            <div class="w-px bg-gray-200 dark:bg-neutral-600 hidden sm:block"></div>

            {{-- Notice of Award Date --}}
            <div class="px-5 py-4 flex flex-col justify-center">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Notice of Award Date</p>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ $noticeOfAward ? \Carbon\Carbon::parse($noticeOfAward)->format('M d, Y') : '—' }}
                </p>
            </div>


            {{-- Date Forwarded --}}
            <div class="px-5 py-4 flex flex-col justify-center">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Date Forwarded</p>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ $date_forwarded ? \Carbon\Carbon::parse($date_forwarded)->format('M d, Y') : '—' }}
                </p>
            </div>

            {{-- Divider --}}
            <div class="w-px bg-gray-200 dark:bg-neutral-600 hidden sm:block"></div>

            {{-- NOA Remarks --}}
            <div class="flex-1 min-w-0 px-5 py-4 flex flex-col justify-center">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Remarks</p>
                <div class="flex items-center gap-2 min-w-0">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate min-w-0 flex-1"
                        title="{{ $noaRemarks ?? '' }}">
                        {{ $noaRemarks ?: '—' }}
                    </p>
                    <button wire:click="openEditNoaRemarksModal"
                        class="shrink-0 p-1 rounded text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors"
                        title="Edit NOA remarks">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-.707.464l-3.121 1.04 1.04-3.121A2 2 0 019 13z" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Selection Banner --}}
    @if (count($selectedItems) > 0)
        <div
            class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 dark:border-emerald-600 rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">
                            {{ count($selectedItems) }} item(s) selected
                        </p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">Ready for bulk editing</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="clearSelections" type="button"
                        class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-neutral-800 dark:text-gray-300 dark:border-neutral-600 dark:hover:bg-neutral-700">
                        Clear Selection
                    </button>
                    <button wire:click="openBulkEditModal" type="button"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>Edit
                        </span>
                    </button>
                    @if ($allSelectedComplete && $anySelectedNotYetForwarded)
                        <button wire:click="openForwardConfirm" type="button"
                            class="px-5 py-2 text-sm font-semibold rounded-lg shadow-lg focus:ring-2 focus:ring-offset-2 transition-all duration-200 transform
                            bg-blue-600 hover:bg-blue-700 text-white hover:shadow-xl hover:scale-105 focus:ring-blue-500">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                </svg>
                                Forward to Supply
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Linked PRs / Items --}}
    <div
        class="bg-white rounded-xl shadow-md border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700 overflow-hidden">

        {{-- Header Section --}}
        <div
            class="px-4 py-4 bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-900/20 border-b-2 border-emerald-500 dark:border-emerald-600">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-emerald-600 dark:bg-emerald-700 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-100">Linked PRs / Items
                        @php $totalLinked = $editPaginator->total(); @endphp
                        <span
                            class="ml-1 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-200 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-200">{{ $totalLinked }}</span>
                    </h3>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">Select items to bulk-edit PMU
                        contract details</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
            <table class="w-full text-xs min-w-max">
                <thead class="sticky top-0 bg-gray-200 dark:bg-neutral-800 z-10">
                    <tr>
                        <th class="px-3 py-3 w-16 text-center">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            PR Number</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Title / Description</th>
                        <th
                            class="px-3 py-3 text-right font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            ABC / Amount</th>
                        <th
                            class="px-3 py-3 text-right font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Awarded Amount</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Supplier</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Date Receipt of Supplier (NOA)</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            PO Date Deadline</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            PO Date</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            PO / Contract No.</th>
                        <th
                            class="px-3 py-3 text-right font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Contract Amount</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Contract Signing Date</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Notice to Proceed Date</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Date Receipt of PO / NTP from Supplier</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Stamped Received of COA</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            PO / Contract No. Link</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            NTP Link</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            Remarks</th>
                        <th
                            class="px-3 py-3 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 whitespace-nowrap">
                            PO Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                    @forelse ($editPaginator as $row)
                        @php $isForwardedToSupply = ($pmuPoByProcId->get($row->rowKey)?->manual_status ?? null) === 'forwarded_to_supply'; @endphp
                        <tr
                            class="hover:bg-emerald-50 dark:hover:bg-neutral-800 transition-colors
                            {{ $isForwardedToSupply ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'bg-white dark:bg-neutral-700' }}
                            {{ in_array($row->rowKey, $selectedItems) ? '!bg-emerald-50 dark:!bg-emerald-900/20' : '' }}">
                            {{-- Checkbox --}}
                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                @if ($isForwardedToSupply)
                                    @php $forwardedAt = $pmuPoByProcId->get($row->rowKey)?->forwarded_to_supply_at; @endphp
                                    <div class="relative flex justify-center items-center gap-1.5" x-data="{ open: false }"
                                        @click.outside="open = false">
                                        @can('update_pmu')
                                            <input type="checkbox" wire:model.live="selectedItems"
                                                value="{{ $row->rowKey }}"
                                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                        @endcan
                                        <button type="button" @click="open = !open" @mouseenter="open = true"
                                            @mouseleave="open = false"
                                            class="p-1 rounded-full bg-indigo-100 dark:bg-indigo-900/40 hover:bg-indigo-200 dark:hover:bg-indigo-800/60 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                            title="Forwarded to Supply">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute left-8 top-1/2 -translate-y-1/2 z-50 w-52 bg-white dark:bg-neutral-800 border border-indigo-200 dark:border-indigo-700 rounded-lg shadow-lg p-3 text-left pointer-events-none"
                                            style="display: none;">
                                            <div class="flex items-center gap-1.5 mb-1.5">
                                                <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                </svg>
                                                <span
                                                    class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">Forwarded
                                                    to Supply</span>
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">Date:</span>
                                                {{ $forwardedAt ? \Carbon\Carbon::parse($forwardedAt)->setTimezone('Asia/Manila')->format('F d, Y g:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <input type="checkbox" wire:model.live="selectedItems"
                                        value="{{ $row->rowKey }}"
                                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                @can('view_procurement')
                                    <a href="{{ route('procurements.view', ['procurement' => $row->procID]) }}"
                                        target="_blank" title="View procurement"
                                        class="inline-flex items-center px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-md font-mono text-xs text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 hover:border-emerald-400 transition-colors">
                                        {{ $row->pr_number }}
                                    </a>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-md font-mono text-xs text-emerald-700 dark:text-emerald-300">
                                        {{ $row->pr_number }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-900 dark:text-white max-w-[200px]">
                                <div class="whitespace-nowrap overflow-hidden text-ellipsis"
                                    title="{{ $row->description }}">{{ $row->description }}</div>
                            </td>
                            <td
                                class="px-3 py-2 whitespace-nowrap text-right text-xs font-medium text-gray-900 dark:text-white">
                                ₱ {{ number_format($row->abc, 2) }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-right text-xs text-gray-900 dark:text-white">
                                {{ $row->awarded_amount ? '₱ ' . number_format($row->awarded_amount, 2) : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $row->supplier_name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $row->date_receipt_of_supplier_noa ? \Carbon\Carbon::parse($row->date_receipt_of_supplier_noa)->format('M d, Y') : '—' }}
                            </td>
                            {{-- Read-only PMU fields --}}
                            @php
                                $po = $pmuPoByProcId->get($row->rowKey);
                                $deadlineWarning = null;
                                if ($po?->po_date_deadline) {
                                    $deadline = \Carbon\Carbon::parse($po->po_date_deadline);
                                    $today = \Carbon\Carbon::today();
                                    $daysUntil = $today->diffInDays($deadline, false);
                                    if ($po->po_date && \Carbon\Carbon::parse($po->po_date)->gt($deadline)) {
                                        $deadlineWarning = 'exceeded';
                                    } elseif ($daysUntil < 0 && !$po->po_date) {
                                        $deadlineWarning = 'overdue';
                                    } elseif ($daysUntil >= 0 && $daysUntil <= 3 && !$po->po_date) {
                                        $deadlineWarning = 'soon';
                                    }
                                }
                            @endphp
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                <div class="flex flex-col gap-1">
                                    <span>{{ $po?->po_date_deadline ? \Carbon\Carbon::parse($po->po_date_deadline)->format('M d, Y') : '—' }}</span>
                                    @if ($deadlineWarning === 'exceeded')
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Exceeded
                                        </span>
                                    @elseif ($deadlineWarning === 'overdue')
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Overdue
                                        </span>
                                    @elseif ($deadlineWarning === 'soon')
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Due Soon
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->po_date ? \Carbon\Carbon::parse($po->po_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->po_contract_number ?? '—' }}
                            </td>
                            <td
                                class="px-3 py-2 whitespace-nowrap text-right text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->contract_amount ? '₱ ' . number_format($po->contract_amount, 2) : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->contract_signing_date ? \Carbon\Carbon::parse($po->contract_signing_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->notice_to_proceed_date ? \Carbon\Carbon::parse($po->notice_to_proceed_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->date_po_receipt_by_supplier ? \Carbon\Carbon::parse($po->date_po_receipt_by_supplier)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-700 dark:text-gray-300">
                                {{ $po?->date_coa_stamped_received ? \Carbon\Carbon::parse($po->date_coa_stamped_received)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                @if ($po?->po_contract_number_link)
                                    <a href="{{ $po->po_contract_number_link }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline"
                                        title="{{ $po->po_contract_number_link }}">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Link
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                @if ($po?->ntp_link)
                                    <a href="{{ $po->ntp_link }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline"
                                        title="{{ $po->ntp_link }}">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Link
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                <span class="block truncate max-w-[12rem]"
                                    title="{{ $po?->remarks ?? '' }}">{{ $po?->remarks ?? '—' }}</span>
                            </td>
                            {{-- Status Column --}}
                            <td class="px-3 py-2 whitespace-nowrap">
                                @php
                                    $manualStatus = $po?->manual_status ?? null;
                                    $manualStatusLabel = match ($manualStatus) {
                                        'return_to_bac' => 'Return to BAC',
                                        'for_end_user_compliance' => 'For End-User Compliance',
                                        'forwarded_to_supply' => 'Forwarded to Supply',
                                        default => null,
                                    };

                                    // Compute auto status based on filled fields
                                    $isComplete = $completedRows[$row->rowKey] ?? false;
                                    $hasPoDate = !empty($po?->po_date);
                                    $hasPoNumber = !empty($po?->po_contract_number);
                                    $hasContractAmount =
                                        !empty($po?->contract_amount) && (float) $po->contract_amount > 0;

                                    if ($isComplete) {
                                        $autoStatusLabel = 'Ready to Forward';
                                        $autoStatusClass =
                                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
                                    } elseif ($hasPoDate && $hasPoNumber && $hasContractAmount) {
                                        $autoStatusLabel = 'For Approval of HOPE';
                                        $autoStatusClass =
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
                                    } elseif ($hasPoDate && $hasPoNumber) {
                                        $autoStatusLabel = 'PO Preparation';
                                        $autoStatusClass =
                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
                                    } else {
                                        $autoStatusLabel = null;
                                        $autoStatusClass = '';
                                    }
                                @endphp
                                @if ($manualStatus === 'forwarded_to_supply')
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        Forwarded to Supply
                                    </span>
                                @elseif ($isComplete)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Ready to Forward
                                    </span>
                                @elseif ($manualStatusLabel)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $manualStatus === 'return_to_bac' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300' }}">
                                        {{ $manualStatusLabel }}
                                    </span>
                                @elseif ($autoStatusLabel)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $autoStatusClass }}">
                                        {{ $autoStatusLabel }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No linked PRs or items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Linked PRs pagination --}}
            @if ($editPaginator->hasPages() || $editPaginator->total() > 0)
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full p-4 border-t border-gray-200 dark:border-neutral-700 gap-3 bg-gradient-to-r from-gray-50 to-white dark:from-neutral-900 dark:to-neutral-800">

                    <!-- Left: Per-page selector -->
                    <div class="flex items-center gap-x-2">
                        <label for="editPerPage"
                            class="text-xs font-medium text-gray-600 dark:text-gray-300">Show</label>
                        <select id="editPerPage" wire:model.live="editPerPage"
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
                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $editPaginator->firstItem() ?? 0 }}</span>
                            to
                            <span
                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $editPaginator->lastItem() ?? 0 }}</span>
                            of
                            <span
                                class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $editPaginator->total() }}</span>
                            items
                        </div>

                        @if ($editPaginator->hasPages())
                            <div class="flex items-center gap-1">
                                <button wire:click="setEditPage({{ $editPaginator->currentPage() - 1 }})"
                                    @disabled($editPaginator->onFirstPage())
                                    class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Previous
                                </button>
                                @for ($p = 1; $p <= $editPaginator->lastPage(); $p++)
                                    <button wire:click="setEditPage({{ $p }})"
                                        class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors duration-150 {{ $p === $editPaginator->currentPage() ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : 'border-gray-300 hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white' }}">
                                        {{ $p }}
                                    </button>
                                @endfor
                                <button wire:click="setEditPage({{ $editPaginator->currentPage() + 1 }})"
                                    @disabled(!$editPaginator->hasMorePages())
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

    {{-- Bulk Edit Modal --}}
    <x-forms.modal :model="'showBulkEditModal'" :closeMethod="'closeBulkEditModal'" title="Bulk Edit — PO / Contract Details" size="max-w-4xl">

        <div class="px-4 py-3">

            {{-- PO Date Deadline Warning Banner --}}
            @if ($po_date_deadline_display)
                <div
                    class="mb-4 flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-600 rounded-lg">
                    <svg class="w-5 h-5 text-amber-500 dark:text-amber-400 shrink-0 mt-0.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">PO Date Deadline:
                            <span
                                class="font-bold">{{ \Carbon\Carbon::parse($po_date_deadline_display)->format('M d, Y') }}</span>
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">

                {{-- PO Date --}}
                <div>
                    <label for="modal_po_date"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        PO Date
                        {{-- @if ($po_date_deadline_display)
                            <span class="text-amber-600 dark:text-amber-400 font-normal">(Deadline:
                                {{ \Carbon\Carbon::parse($po_date_deadline_display)->format('M d, Y') }})</span>
                        @endif --}}
                    </label>
                    <input type="date" id="modal_po_date" wire:model="po_date"
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('po_date') border-red-500 @else border-gray-300 @enderror">
                    @error('po_date')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PO / Contract Number --}}
                <div>
                    <label for="modal_po_contract_number"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        PO / Contract Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="modal_po_contract_number" wire:model="po_contract_number"
                        placeholder="e.g. 2026-01-0001"
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('po_contract_number') border-red-500 @else border-gray-300 @enderror">
                    @error('po_contract_number')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contract Amount --}}
                <div x-data="{
                    display: '',
                    focused: false,
                    init() {
                        const raw = $wire.contract_amount;
                        this.display = raw ? this.format(raw) : '';
                        $wire.$watch('contract_amount', (val) => {
                            if (!this.focused) {
                                this.display = val ? this.format(val) : '';
                            }
                        });
                    },
                    format(val) {
                        let n = parseFloat(String(val).replace(/,/g, ''));
                        return isNaN(n) ? '' : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                    handleInput(e) {
                        let raw = e.target.value.replace(/[^0-9.]/g, '');
                        let parts = raw.split('.');
                        if (parts.length > 2) raw = parts[0] + '.' + parts.slice(1).join('');
                        this.display = raw;
                    },
                    handleBlur() {
                        this.focused = false;
                        let raw = String(this.display).replace(/,/g, '');
                        let n = parseFloat(raw);
                        if (!isNaN(n)) {
                            this.display = this.format(n);
                            $wire.set('contract_amount', n);
                        } else {
                            this.display = '';
                            $wire.set('contract_amount', '');
                        }
                    }
                }">
                    <label for="modal_contract_amount"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Contract Amount
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-500 dark:text-gray-400 pointer-events-none">₱</span>
                        <input type="text" id="modal_contract_amount" x-model="display"
                            x-on:focus="focused = true" x-on:input="handleInput($event)" x-on:blur="handleBlur()"
                            placeholder="0.00"
                            class="w-full pl-6 pr-3 py-2 text-xs text-right border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                            @error('contract_amount') border-red-500 @else border-gray-300 @enderror">
                    </div>
                    @error('contract_amount')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contract Signing Date --}}
                <div>
                    <label for="modal_contract_signing_date"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Contract Signing Date
                    </label>
                    <input type="date" id="modal_contract_signing_date" wire:model="contract_signing_date"
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('contract_signing_date') border-red-500 @else border-gray-300 @enderror">
                    @error('contract_signing_date')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notice to Proceed Date --}}
                <div>
                    <label for="modal_notice_to_proceed_date"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Notice to Proceed Date
                    </label>
                    <input type="date" id="modal_notice_to_proceed_date" wire:model="notice_to_proceed_date"
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('notice_to_proceed_date') border-red-500 @else border-gray-300 @enderror">
                    @error('notice_to_proceed_date')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PO / Contract Number Link --}}
                <div class="sm:col-span-6">
                    <label for="modal_po_contract_number_link"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        PO / Contract Number Link
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </span>
                        <input type="url" id="modal_po_contract_number_link" wire:model="po_contract_number_link"
                            placeholder="https://..."
                            class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                            @error('po_contract_number_link') border-red-500 @else border-gray-300 @enderror">
                    </div>
                    @error('po_contract_number_link')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NTP Link --}}
                <div class="sm:col-span-6">
                    <label for="modal_ntp_link"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        NTP Link
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </span>
                        <input type="url" id="modal_ntp_link" wire:model="ntp_link" placeholder="https://..."
                            class="w-full pl-8 pr-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                            @error('ntp_link') border-red-500 @else border-gray-300 @enderror">
                    </div>
                    @error('ntp_link')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Date of PO Receipt by Supplier --}}
                <div>
                    <label for="modal_date_po_receipt_by_supplier"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Date Receipt of PO<br><span>/ NTP from Supplier</span>
                    </label>
                    <input type="date" id="modal_date_po_receipt_by_supplier"
                        wire:model="date_po_receipt_by_supplier"
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('date_po_receipt_by_supplier') border-red-500 @else border-gray-300 @enderror">
                    @error('date_po_receipt_by_supplier')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Stamped Received of COA (date) --}}
                <div>
                    <label for="modal_date_coa_stamped_received"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <br><span>&nbsp;</span>Stamped Received of COA
                    </label>
                    <input type="date" id="modal_date_coa_stamped_received" wire:model="date_coa_stamped_received"
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('date_coa_stamped_received') border-red-500 @else border-gray-300 @enderror">
                    @error('date_coa_stamped_received')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Status --}}
                <div class="sm:col-span-2">
                    <label for="modal_manual_status"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        PO Status
                        <span class="ml-1 font-normal text-gray-400">(leave as "— No Change —" to keep existing
                            per-item status)</span>
                    </label>
                    <select id="modal_manual_status" wire:model="manual_status"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600">
                        <option value="auto">Auto</option>
                        <option value="return_to_bac">Return to BAC</option>
                        <option value="for_end_user_compliance">For End-User Compliance</option>
                    </select>
                    @if ($manual_status !== '' && $manual_status !== 'auto')
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            This will override the computed stage badge for all selected items.
                        </p>
                    @endif
                </div>

                {{-- Remarks --}}
                <div class="sm:col-span-6">
                    <label for="modal_remarks"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Remarks
                    </label>
                    <textarea id="modal_remarks" wire:model="remarks" rows="2" placeholder="Enter any remarks..."
                        class="w-full px-3 py-2 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all dark:bg-neutral-700 dark:text-white dark:border-neutral-600
                        @error('remarks') border-red-500 @else border-gray-300 @enderror"></textarea>
                    @error('remarks')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>


            </div>

            {{-- Footer --}}
            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-neutral-600 flex justify-end gap-3">
                <button type="button" wire:click="closeBulkEditModal"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-neutral-800 dark:text-gray-300 dark:border-neutral-600 dark:hover:bg-neutral-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </button>
                <button type="button" wire:click="update"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save
                </button>
            </div>

        </div>

    </x-forms.modal>




    {{-- Edit NOA Remarks Modal --}}
    @if ($showEditNoaRemarksModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm"
            wire:click.self="closeEditNoaRemarksModal">
            <div
                class="bg-white dark:bg-neutral-800 rounded-xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md mx-4">
                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-5 py-4 border-b border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/20 rounded-t-xl">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-.707.464l-3.121 1.04 1.04-3.121A2 2 0 019 13z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Edit NOA Remarks</h3>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400">{{ $noticeOfAwardNumber }}</p>
                        </div>
                    </div>
                    <button wire:click="closeEditNoaRemarksModal"
                        class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Remarks <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea wire:model="editNoaRemarksValue" rows="4" placeholder="Enter remarks about this NOA..."
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-neutral-700 dark:text-white resize-none
                            @error('editNoaRemarksValue') border-red-400 dark:border-red-500 @else border-gray-300 dark:border-neutral-600 @enderror"></textarea>
                    @error('editNoaRemarksValue')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Footer --}}
                <div
                    class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                    <button wire:click="closeEditNoaRemarksModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="confirmEditNoaRemarks" wire:loading.attr="disabled"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Remarks
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Remarks Modal --}}
    @if ($showEditRemarksModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm"
            wire:click.self="closeEditRemarksModal">
            <div
                class="bg-white dark:bg-neutral-800 rounded-xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md mx-4">
                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-5 py-4 border-b border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/20 rounded-t-xl">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536M9 13l6.5-6.5a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-.707.464l-3.121 1.04 1.04-3.121A2 2 0 019 13z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Edit Remarks</h3>
                    </div>
                    <button wire:click="closeEditRemarksModal"
                        class="p-1.5 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Remarks <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea wire:model="editRemarksValue" rows="4" placeholder="Enter remarks..."
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-neutral-700 dark:text-white resize-none
                            @error('editRemarksValue') border-red-400 dark:border-red-500 @else border-gray-300 dark:border-neutral-600 @enderror"></textarea>
                    @error('editRemarksValue')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Footer --}}
                <div
                    class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                    <button wire:click="closeEditRemarksModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="confirmEditRemarks" wire:loading.attr="disabled"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Remarks
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Forward to Supply Modal --}}
    <x-forms.modal wire:model="showForwardConfirm" title="Forward to Supply" size="max-w-lg"
        model="showForwardConfirm" closeMethod="closeForwardConfirm">
        <div class="px-4 py-3">
            {{-- Summary Pills --}}
            @if ($forwardedToSupplySummary['forwarded'] > 0 || $forwardedToSupplySummary['pending'] > 0)
                <div class="mb-4 flex flex-wrap gap-2">
                    @if ($forwardedToSupplySummary['forwarded'] > 0)
                        <div
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-xs font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $forwardedToSupplySummary['forwarded'] }} already forwarded
                        </div>
                    @endif
                    @if ($forwardedToSupplySummary['pending'] > 0)
                        <div
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 rounded-lg text-xs font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $forwardedToSupplySummary['pending'] }} pending
                        </div>
                    @endif
                </div>
            @endif

            {{-- Already Forwarded Note --}}
            @if ($forwardedToSupplySummary['forwarded'] > 0)
                <div
                    class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg text-xs text-yellow-800 dark:text-yellow-300">
                    <strong>Note:</strong> {{ $forwardedToSupplySummary['forwarded'] }} item(s) already forwarded —
                    their date will be updated to the date you enter below.
                </div>
            @endif

            {{-- Date Input Field --}}
            <div class="mb-4">
                <label for="actualDateForwarded"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Actual Date &amp; Time Forwarded <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" id="actualDateForwarded" wire:model.defer="actualDateForwarded"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white"
                    required>
                @error('actualDateForwarded')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Modal Footer Actions --}}
            <div class="border-t border-gray-200 dark:border-neutral-700 pt-4 mt-4 flex items-center justify-end">
                <div class="flex gap-2">
                    <button type="button" wire:click="closeForwardConfirm"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-600 border border-gray-300 dark:border-neutral-500 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-500 transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="forwardToSupply"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        Confirm Forward
                    </button>
                </div>
            </div>
        </div>
    </x-forms.modal>

    <!-- Bottom Spacer -->
    <div class="h-16"></div>

</div>
