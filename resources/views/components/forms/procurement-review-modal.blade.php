@props([
    'form' => [],
    'categories' => [],
    'divisions' => [],
    'clusterCommittees' => [],
    'venueSpecifics' => [],
    'venueProvinces' => [],
    'endUsers' => [],
    'fundSources' => [],
    'convertingToPerItem' => false,
])

@php
    $lookup = function ($collection, $id, $field) {
        if (empty($id)) {
            return '—';
        }
        $item = collect($collection)->firstWhere('id', $id);
        return $item ? ((string) ($item->{$field} ?? '') ?: '—') : '—';
    };
    $get = function ($key, $default = '—') use ($form) {
        $value = $form[$key] ?? null;
        return ($value === null || $value === '') ? $default : $value;
    };
    $yesNo = fn($v) => $v ? 'Yes' : 'No';
    $fmtDate = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('F j, Y') : '—';

    // Reusable read-only cell (label on top, value below), spanning grid columns.
    $cell = function ($label, $value, $span = 'col-span-1') {
        $label = e($label);
        $value = e(($value === '' || $value === null) ? '—' : $value);
        return '<div class="flex flex-col ' . $span . '">' .
            '<dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">' . $label . '</dt>' .
            '<dd class="mt-0.5 text-sm font-medium text-gray-900 dark:text-white break-words">' . $value . '</dd>' .
            '</div>';
    };

    $isPerItem = ($form['procurement_type'] ?? '') === 'perItem';
    $abc = (float) preg_replace('/[^0-9.]/', '', (string) ($form['abc'] ?? 0));
@endphp

<div x-data="{ open: $wire.entangle('showReview').live }" x-show="open" x-cloak style="display: none;"
    @keydown.escape.window="open = false" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-black/50"></div>

    {{-- Panel --}}
    <div x-show="open" x-transition
        class="relative flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-neutral-800">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-neutral-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Review before saving</h3>
            <button type="button" @click="open = false"
                class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-neutral-700">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-gray-50 px-6 py-4 dark:bg-neutral-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Please double-check the details below. Once you confirm, this record will be saved.
            </p>

            @if ($convertingToPerItem)
                <div
                    class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                    <span class="font-semibold">This PR will be converted from Per Lot to Per Item.</span>
                    {{ count($form['items'] ?? []) }} item(s) will be created, each starting at Stage 1. Tracking will
                    move from the lot to the individual items, and the procurement type cannot be changed back.
                </div>
            @endif

            {{-- Box 1: PR No. / Program & Project / Type / Items --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <dl class="grid grid-cols-2 gap-4 md:grid-cols-10">
                    {!! $cell('PR No.', $get('pr_number'), 'col-span-1') !!}
                    {!! $cell('Procurement Program / Project', $get('procurement_program_project'), 'col-span-2 md:col-span-9') !!}
                    {!! $cell('Procurement Type', $isPerItem ? 'Per Item' : 'Per Lot', 'col-span-1') !!}
                </dl>

                @if ($isPerItem && !empty($form['items']))
                    <div class="mt-4">
                        <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Items
                            ({{ count($form['items']) }})</h4>
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-neutral-700">
                            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-700">
                                    <tr>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                            #</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                            Description</th>
                                        <th
                                            class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                            Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                                    @foreach ($form['items'] as $item)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                                {{ $item['item_no'] ?? '' }}</td>
                                            <td class="px-3 py-2 text-gray-900 dark:text-white">
                                                {{ $item['description'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">
                                                ₱ {{ number_format((float) preg_replace('/[^0-9.]/', '', (string) ($item['amount'] ?? 0)), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Box 2: Receipt / Category / Codes / Division / Cluster --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <dl class="grid grid-cols-2 gap-4 md:grid-cols-8">
                    {!! $cell('Date Receipt', $fmtDate($form['date_receipt'] ?? null), 'col-span-1') !!}
                    {!! $cell('Category', $lookup($categories, $form['category_id'] ?? null, 'category'), 'col-span-2') !!}
                    {!! $cell('Category Type', $get('category_type'), 'col-span-1') !!}
                    {!! $cell('BAC Category', $get('rbac_sbac'), 'col-span-1') !!}
                    {!! $cell('DTRACK #', $get('dtrack_no'), 'col-span-1') !!}
                    {!! $cell('UniCode', $get('unicode'), 'col-span-2') !!}
                    {!! $cell('Division', $lookup($divisions, $form['divisions_id'] ?? null, 'divisions'), 'col-span-2 md:col-span-4') !!}
                    {!! $cell('Cluster / Committee', $lookup($clusterCommittees, $form['cluster_committees_id'] ?? null, 'clustercommittee'), 'col-span-2') !!}
                </dl>
            </div>

            {{-- Box 3: Venue / PPMP / APP --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <dl class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    {!! $cell('Venue (Specific)', $lookup($venueSpecifics, $form['venue_specific_id'] ?? null, 'name'), 'col-span-2') !!}
                    {!! $cell('Venue (Province/HUC)', $lookup($venueProvinces, $form['venue_province_huc_id'] ?? null, 'province_huc'), 'col-span-2') !!}
                    {!! $cell('Category / Venue', $get('category_venue'), 'col-span-2 md:col-span-4') !!}
                    {!! $cell('Approved PPMP', $yesNo($form['approved_ppmp'] ?? false), 'col-span-2') !!}
                    {!! $cell('APP Updated', $yesNo($form['app_updated'] ?? false), 'col-span-2') !!}
                </dl>
            </div>

            {{-- Box 4: Dates Needed / End-User / Early Procurement --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="grid grid-cols-4 gap-4">
                    <div class="col-span-4 flex flex-col gap-4 sm:flex-row md:col-span-3">
                        <div class="flex-1">{!! $cell('Immediate Date Needed', $get('immediate_date_needed')) !!}</div>
                        <div class="flex-1">{!! $cell('Date Needed', $get('date_needed')) !!}</div>
                    </div>
                    <div class="col-span-4 flex flex-col gap-3 md:col-span-1">
                        {!! $cell('PMO / End-User', $lookup($endUsers, $form['end_users_id'] ?? null, 'endusers')) !!}
                        {!! $cell('Early Procurement', $yesNo($form['early_procurement'] ?? false)) !!}
                    </div>
                </div>
            </div>

            {{-- Box 5: Source of Funds / Expense Class / ABC / 50k --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <dl class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    {!! $cell('Source of Funds', $lookup($fundSources, $form['fund_source_id'] ?? null, 'fundsources'), 'col-span-1') !!}
                    {!! $cell('Expense Class', $get('expense_class'), 'col-span-1') !!}
                    {!! $cell('ABC Amount', '₱ ' . number_format($abc, 2), 'col-span-1') !!}
                    {!! $cell('ABC ⇔ 50k', $get('abc_50k'), 'col-span-1') !!}
                </dl>
            </div>
        </div>

        {{-- Footer --}}
        <div
            class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-neutral-700">
            <button type="button" @click="open = false"
                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-neutral-700 dark:text-gray-200 dark:hover:bg-neutral-600">
                Go Back &amp; Edit
            </button>
            <button type="button" wire:click="confirmSave" wire:target="confirmSave" wire:loading.attr="disabled"
                class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                <svg wire:loading.remove wire:target="confirmSave" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg wire:loading wire:target="confirmSave" class="h-4 w-4 animate-spin" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span wire:loading.remove wire:target="confirmSave">Confirm &amp; Save</span>
                <span wire:loading wire:target="confirmSave">Saving…</span>
            </button>
        </div>
    </div>
</div>
