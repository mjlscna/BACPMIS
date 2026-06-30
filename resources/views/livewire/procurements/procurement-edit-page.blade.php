<div class="space-y-6 p-2 pb-[5rem]">

    {{-- First Box --}}
    <div
        class="bg-white p-4 rounded-xl shadow border border-gray-200
                dark:bg-neutral-700 dark:border-neutral-700 ">
        <!-- Grid for PR No. + Program/Project -->
        <div class="grid grid-cols-2 md:grid-cols-10 gap-4">
            <!-- PR Number -->
            <div class="col-span-1">
                <x-forms.input id="pr_number" label="PR No." model="form.pr_number" :form="$form" :required="true"
                    textAlign="right" :readonly="false" :disabled="false" />
            </div>

            <!-- Procurement Program / Project -->
            <div class="col-span-9">
                <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                    model="form.procurement_program_project" :required="true" :rows="$textareaRows" :readonly="false"
                    :autoResize="true" />
            </div>
        </div>

        <!-- Per Lot / Per Item Toggle + Table -->
        <div class="mt-6">
            <!-- Toggle -->
            <div class="flex items-center gap-x-3 mb-4">
                <x-forms.prType id="procurement-toggle" model="form.procurement_type" :form="$form"
                    :clickable="false" />
                @if ($form['procurement_type'] === 'perItem')
                    {{-- Show/Hide table button --}}
                    <button type="button" wire:click="$toggle('showTable')"
                        class="p-2 rounded-lg border transition-all duration-200
                        {{ $showTable
                            ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-900/30'
                            : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50 dark:bg-neutral-700 dark:border-neutral-600 dark:text-gray-300 dark:hover:bg-neutral-600' }}">
                        @if (!$showTable)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        @endif
                    </button>
                @endif
            </div>
            <!-- Table shows only when "Per Item" is selected -->
            @if ($form['procurement_type'] === 'perItem')
                <div class="w-full">

                    @if ($showTable)
                        <div
                            class="bg-gradient-to-br from-gray-50 to-white dark:from-neutral-800 dark:to-neutral-700 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-600 overflow-hidden">
                            <!-- Table Header -->
                            <div
                                class="bg-white dark:bg-neutral-700 border-b border-gray-200 dark:border-neutral-600 px-6 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Item List
                                        </h2>
                                    </div>
                                    <div class="flex items-center gap-3 flex-1 justify-end">
                                        <input type="text" wire:model.live.debounce.300ms="searchItem"
                                            placeholder="Search items..."
                                            class="w-full max-w-xs px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-800 dark:border-neutral-600 dark:text-white">
                                        <button type="button" wire:click="addItem"
                                            class="p-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-800 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Items table component --}}
                            @if ($form['procurement_type'] === 'perItem')
                                @php
                                    $paginatedData = $this->paginatedItems;
                                    // Get filtered items with preserved keys
                                    $searchLower = strtolower($searchItem ?? '');
                                    $filteredItems = empty($searchItem)
                                        ? $form['items']
                                        : array_filter($form['items'] ?? [], function ($item) use ($searchLower) {
                                            return str_contains(strtolower($item['description'] ?? ''), $searchLower) ||
                                                str_contains(
                                                    strtolower((string) ($item['item_no'] ?? '')),
                                                    $searchLower,
                                                );
                                        });
                                @endphp
                                <x-forms.prItems-table :form="$form" model="form.items" :page="$page"
                                    :per-page="$perPage" :filteredItems="$filteredItems" />
                            @endif

                            {{-- Pagination --}}
                            @php
                                $paginationData = $this->paginatedItems;
                                $totalItems = $paginationData['total'];
                                $totalPages = max(1, ceil($totalItems / $perPage));
                            @endphp

                            @if ($totalItems > 0)
                                <!-- Table Footer -->
                                <div
                                    class="bg-white dark:bg-neutral-700 border-t border-gray-200 dark:border-neutral-600 px-6 py-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <!-- Left: Per-page selector -->
                                        <div class="flex items-center gap-x-2">
                                            <label
                                                class="text-xs font-medium text-gray-600 dark:text-gray-300">Show</label>
                                            <select wire:model.live="perPage"
                                                class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 dark:bg-neutral-700 dark:text-white dark:border-neutral-600">
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">per page</span>
                                        </div>

                                        <!-- Center: Pagination -->
                                        <div class="flex flex-col items-center gap-2 flex-1">
                                            <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                                Showing <span
                                                    class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $paginationData['from'] }}</span>
                                                to
                                                <span
                                                    class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $paginationData['to'] }}</span>
                                                of
                                                <span
                                                    class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $totalItems }}</span>
                                                items
                                            </div>

                                            @if ($totalPages > 1)
                                                <div class="flex items-center gap-1">
                                                    <!-- Previous Button -->
                                                    <button type="button"
                                                        wire:click="$set('page', {{ max(1, $page - 1) }})"
                                                        @if ($page <= 1) disabled @endif
                                                        class="p-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 19l-7-7 7-7" />
                                                        </svg>
                                                    </button>

                                                    <!-- Page Numbers -->
                                                    @for ($i = 1; $i <= $totalPages; $i++)
                                                        @if ($i == 1 || $i == $totalPages || abs($i - $page) <= 2)
                                                            <button type="button"
                                                                wire:click="$set('page', {{ $i }})"
                                                                class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors duration-150 {{ $page == $i ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : 'border-gray-300 hover:bg-gray-100 dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white' }}">
                                                                {{ $i }}
                                                            </button>
                                                        @elseif (abs($i - $page) == 3)
                                                            <span class="px-2 text-xs text-gray-500">...</span>
                                                        @endif
                                                    @endfor

                                                    <!-- Next Button -->
                                                    <button type="button"
                                                        wire:click="$set('page', {{ min($totalPages, $page + 1) }})"
                                                        @if ($page >= $totalPages) disabled @endif
                                                        class="p-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed dark:border-neutral-600 dark:hover:bg-neutral-700 dark:text-white transition-colors duration-150">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <span class="font-medium">No items found</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <div class="grid grid-cols-2 md:grid-cols-8 gap-4">
            <!-- Date Receipt (Advance Copy) -->
            <x-forms.date id="date_receipt" label="Date Receipt" model="form.date_receipt" :form="$form"
                :required="true" colspan="col-span-1" />
            <!-- Category -->
            <x-forms.select id="category_id" label="Category" model="form.category_id" :form="$form"
                :options="$categories" optionValue="id" optionLabel="category" :required="true" wireModifier="live"
                colspan="col-span-2" :searchable="true" />
            <!-- Category Type (Read-only) -->
            <x-forms.readonly-input id="category_type" label="Category Type" model="form.category_type"
                :form="$form" :required="false" :colspan="1" />
            <!-- RBAC / SBAC (Read-only) -->
            <x-forms.readonly-input id="rbac_sbac" label="BAC Category" model="form.rbac_sbac" :form="$form"
                :required="false" :colspan="1" />
            <!-- DTRACK Number -->
            <x-forms.input id="dtrack_no" label="DTRACK #" model="form.dtrack_no" :form="$form"
                colspan="col-span-1" />
            <!-- UniCode -->
            <x-forms.input id="unicode" label="UniCode" model="form.unicode" :form="$form" :required="true"
                colspan="col-span-2" />
            <!-- Division -->
            <x-forms.select id="divisions_id" label="Division" model="form.divisions_id" :form="$form"
                :options="$divisions" optionValue="id" optionLabel="divisions" :required="true" colspan="col-span-4"
                :searchable="true" />
            <!-- Cluster / Committee -->
            <x-forms.select id="cluster_committees_id" label="Cluster / Committee" model="form.cluster_committees_id"
                :form="$form" :options="$clusterCommittees" optionValue="id" optionLabel="clustercommittee"
                :required="true" colspan="col-span-2" :searchable="true" />

        </div>
    </div>
    <div
        class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6 dark:bg-neutral-700 dark:border-neutral-700">
        <!-- Simple Form Fields in Landscape Layout -->
        <div class="grid grid-cols-4 gap-4">
            <!-- Venue Specific -->
            <x-forms.select id="venue_specific_id" label="Venue|Specific" model="form.venue_specific_id"
                :form="$form" :options="$venueSpecifics" optionValue="id" optionLabel="name" :required="false"
                colspan="col-span-2" :searchable="true" />
            <!-- Venue Province/HUC -->
            <x-forms.select id="venue_province_huc_id" label="Venue|Province/HUC" model="form.venue_province_huc_id"
                :form="$form" :options="$venueProvinces" optionValue="id" optionLabel="province_huc" :required="false"
                colspan="col-span-2" :searchable="true" />
            <!-- Category / Venue (Read-only) -->
            <x-forms.readonly-input id="category_venue" label="Category / Venue" model="form.category_venue"
                :form="$form" :required="false" colspan="col-span-4" />
            <!-- Approved PPMP -->
            <div class="flex flex-col col-span-2">
                <x-forms.approved-ppmp :form="$form" model="form.approved_ppmp" othersModel="otherPPMP" />
            </div>
            <div class="flex flex-col col-span-2">
                <!-- APP Updated -->
                <x-forms.app-updated :form="$form" model="form.app_updated" othersModel="otherAPP" />
            </div>
        </div>
    </div>
    <div
        class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6 dark:bg-neutral-700 dark:border-neutral-700">
        <div class="grid grid-cols-4 gap-4">
            <!-- LEFT COLUMN -->
            <div class="col-span-3 flex gap-4">
                <!-- Immediate Date Needed -->
                <div class="flex-1">
                    <x-forms.textarea id="immediate_date_needed" label="Immediate Date Needed"
                        model="form.immediate_date_needed" :form="$form" :maxlength="500" rows="4"
                        :required="true" :autoResize="true" />

                </div>

                <!-- Date Needed -->
                <div class="flex-1">
                    <x-forms.textarea id="date_needed" label="Date Needed" model="form.date_needed"
                        :form="$form" :required="true" :maxlength="500" rows="4" :autoResize="true" />
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-span-1 flex flex-col gap-4">
                <!-- PMO/End-User -->
                <div>
                    <x-forms.select id="end_users_id" label="PMO/End-User" model="form.end_users_id"
                        :form="$form" :options="$endUsers" optionValue="id" optionLabel="endusers"
                        :required="true" :searchable="true" />

                </div>
                <!-- Early Procurement Toggle -->
                <div>
                    <x-forms.early-procurement model="form.early_procurement" :form="$form" :clickable="true" />
                </div>
            </div>
        </div>

    </div>
    <div class="flex justify-center gap-4 mt-6">
        <!-- Third Box -->
        <div class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
            <!-- Simple Form Fields in Landscape Layout -->
            <div class="grid grid-cols-4 gap-4">
                <!-- Source of Funds -->
                <div class="col-span-1">
                    <x-forms.select id="fund_source_id" label="Source of Funds" model="form.fund_source_id"
                        :form="$form" :options="$fundSources" optionValue="id" optionLabel="fundsources"
                        :required="true" :searchable="true" />
                </div>

                <!-- Expense Class -->
                <div class="col-span-1">
                    <x-forms.input id="expense_class" label="Expense Class" model="form.expense_class"
                        :form="$form" :required="false" textAlign="right" />
                </div>

                <!-- ABC Amount -->
                <div class="col-span-1">
                    @if ($form['procurement_type'] === 'perLot')
                        <x-forms.abc-lot :form="$form" label="ABC Amount" model="abc" :required="true" />
                    @elseif($form['procurement_type'] === 'perItem')
                        <x-forms.abc-item :form="$form" label="ABC Amount" />
                    @endif
                </div>
                <!-- ABC ⇔ 50k -->
                <div class="col-span-1">
                    <x-forms.abc50k id="abc_50k" label="ABC ⇔ 50k" model="form.abc_50k" :form="$form" />

                </div>

            </div>
        </div>
    </div>
    <div
        class="fixed bottom-4 right-0 left-0 lg:left-48  flex justify-end p-2 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-700 z-30">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-end gap-3">
            <button wire:click="cancel" wire:target="save" wire:loading.attr="disabled"
                class="flex items-center gap-2 px-2 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600 disabled:opacity-60 disabled:cursor-not-allowed">
                Cancel
            </button>
            <button wire:click="save" wire:target="save" wire:loading.attr="disabled"
                class="flex items-center gap-2 px-2 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                    </path>
                </svg>
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </div>

    {{-- Review-before-save confirmation modal --}}
    <x-forms.procurement-review-modal :form="$form" :categories="$categories" :divisions="$divisions" :clusterCommittees="$clusterCommittees"
        :venueSpecifics="$venueSpecifics" :venueProvinces="$venueProvinces" :endUsers="$endUsers" :fundSources="$fundSources" />
</div>
