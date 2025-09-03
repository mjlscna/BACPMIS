<div>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-emerald-600/20 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg w-full max-w-max mx-4 sm:mx-auto transition-all overflow-hidden max-h-[90vh]">

            <!-- Header -->
            <div class="flex justify-between items-center p-2 border-gray-200 bg-emerald-600 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-white ml-2">Procurement</h2>
                <button wire:click="$set('showCreateModal', false)"
                    class="text-red-600 hover:text-red-700 dark:text-white dark:hover:text-gray-100 ">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="relative ">
                <!-- Tab Navigation (within modal content area) -->
                <!-- Stepper Navigation -->

                @php
                $canAccessTab2 = !empty($form['modes'][0]['mode_of_procurement_id'] ?? null) && !empty($procID);
                $canAccessTab3 = !empty($procID) && ($hasSuccessfulBidOrNtf || $hasSuccessfulSvp);
                @endphp

                <div class="flex justify-center w-full border-b border-emerald-500">
                    <ul class="flex items-center w-full max-w-7xl py-2 bg-white dark:bg-neutral-800 dark:border-neutral-700 mx-48"
                        data-hs-stepper='{"isCompleted": true}'>

                        <!-- Step 1 -->
                        <li class="flex items-center gap-x-2 basis-1/3 group"
                            data-hs-stepper-nav-item='{"index": 1, "isCompleted": {{ $activeTab > 1 ? ' true' : 'false'
                            }} }'>
                            <button type="button" wire:click="switchTab(1)"
                                class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 1 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 1 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                                1
                            </button>
                            <span class="text-sm font-medium text-gray-800 dark:text-white whitespace-nowrap">
                                Purchase Request
                            </span>
                            <!-- Dynamic Line -->
                            <div class="h-px w-48 ml-3 mr-3 transition-colors duration-300
            {{ $activeTab > 1 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-700' }}">
                            </div>
                        </li>

                        <!-- Step 2 -->
                        <li class="flex items-center gap-x-2 basis-1/3 group"
                            data-hs-stepper-nav-item='{"index": 2, "isCompleted": {{ $activeTab > 2 ? ' true' : 'false'
                            }} }'>
                            <button type="button" @if ($canAccessTab2) wire:click="switchTab(2)" @endif
                                class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 2 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($canAccessTab2 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-neutral-400 cursor-not-allowed') }}"
                                @if (!$canAccessTab2) disabled @endif>
                                2
                            </button>
                            <span
                                class="text-sm font-medium whitespace-nowrap {{ $canAccessTab2 ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                                Mode of Procurement
                            </span>
                            <!-- Dynamic Line -->
                            <div class="h-px w-48 ml-3 mr-3 transition-colors duration-300
            {{ $activeTab > 2 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-700' }}">
                            </div>
                        </li>

                        <!-- Step 3 -->
                        <li class="flex items-center gap-x-2 basis-1/3 group"
                            data-hs-stepper-nav-item='{"index": 3, "isCompleted": {{ $activeTab > 3 ? ' true' : 'false'
                            }} }'>
                            <button type="button" @if ($canAccessTab3) wire:click="switchTab(3)" @endif
                                class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 3 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($canAccessTab3 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-neutral-400 cursor-not-allowed') }}"
                                @if (!$canAccessTab3) disabled aria-disabled="true"
                                title="You need a successful bid or Mode 5 to access this tab." @endif>
                                3
                            </button>
                            <span
                                class="text-sm font-medium whitespace-nowrap {{ $canAccessTab3 ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                                Post
                            </span>
                        </li>
                    </ul>

                </div>

                <!-- Tab Contents inside bordered div with padding and border -->
                <div class="border px-4 py-2 border-gray-200 dark:border-neutral-700 max-h-[65vh] overflow-y-auto">
                    <!-- PR -->
                    <div id="card-type-tab-preview" role="tabpanel" aria-labelledby="card-type-tab-item-1"
                        class="{{ $activeTab === 1 ? '' : 'hidden' }} mb-4 mt-4">

                        <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                            <!-- Grid for PR No. + Program/Project -->
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <!-- PR Number -->
                                <div class="col-span-1">
                                    <label for="pr_number" class="block text-sm font-medium text-gray-700 mb-1">
                                        PR No.
                                    </label>
                                    <x-forms.readonly-input id="pr_number" model="form.pr_number" :form="$form"
                                        :viewOnly="$viewOnlyTab1" :required="true" :colspan="1" textAlign="right"
                                        class="flex-1" />
                                </div>

                                <!-- Procurement Program / Project -->
                                <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                                    model="form.procurement_program_project" :form="$form" :required="true"
                                    :viewOnly="$viewOnlyTab1" :maxlength="500" :rows="1" colspan="col-span-4" />
                            </div>

                            <!-- Per Lot / Per Item Toggle + Table -->
                            <div class="mt-6 flex flex-col md:flex-row md:items-start md:space-x-6">
                                <!-- Toggle -->
                                <div class="flex items-center gap-x-3">
                                    <label class="text-sm text-gray-500">Per Lot</label>

                                    <label class="relative inline-block w-11 h-6 cursor-pointer">
                                        <input type="checkbox" class="peer sr-only"
                                            wire:model.defer="form.procurement_type"
                                            @change="$event.target.checked ? @this.set('form.procurement_type', 'item') : @this.set('form.procurement_type', 'lot')"
                                            {{ $form['procurement_type']==='item' ? 'checked' : '' }}>
                                        <span class="absolute inset-0 bg-blue-600 rounded-full transition-colors duration-200 ease-in-out
                     peer-checked:bg-emerald-600"></span>
                                        <span class="absolute top-1/2 start-0.5 -translate-y-1/2 size-5 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out
                     peer-checked:translate-x-full"></span>
                                    </label>

                                    <label class="text-sm text-gray-500">Per Item</label>
                                </div>


                                <!-- Table shows only when "Per Item" is selected -->
                                @if($form['procurement_type'] === 'item')
                                <div class="flex-1">
                                    {{-- Header row --}}
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex items-center gap-x-2">
                                            {{-- Show/Hide table button --}}
                                            <button type="button" wire:click="$toggle('showTable')"
                                                class="transition p-1 rounded-full border border-gray-300 hover:bg-gray-100">
                                                @if (!$showTable)
                                                {{-- Expand icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                @else
                                                {{-- Collapse icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                                @endif
                                            </button>
                                            <h3 class="font-semibold text-gray-700">Item List</h3>
                                        </div>


                                    </div>


                                    {{-- Table --}}@if($showTable)
                                    <div class="overflow-x-auto">
                                        {{-- Add Item button --}}<div class="flex justify-end mb-2">
                                            <button type="button" wire:click="addItem"
                                                class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14" />
                                                    <path d="M12 5v14" />
                                                </svg>Item
                                            </button>
                                        </div>
                                        <table class="min-w-[600px] divide-y divide-gray-200 rounded-xl w-full">
                                            <thead class="bg-gray-50 sticky top-0 z-40">
                                                <tr>
                                                    <th
                                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-28">
                                                        Item No
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                        Description
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($form['items'] as $index => $item)
                                                <tr>
                                                    {{-- Item No --}}
                                                    <td class="px-6 py-4 text-center text-sm text-gray-800">
                                                        <input type="text"
                                                            class="border border-gray-300 rounded-lg px-2 py-1 w-20 focus:ring-emerald-500 focus:border-emerald-500 text-center"
                                                            placeholder="#"
                                                            wire:model.defer="form.items.{{ $index }}.item_no">
                                                    </td>

                                                    {{-- Description --}}
                                                    <td class="px-6 py-4 text-sm text-gray-800">
                                                        <input type="text"
                                                            class="border border-gray-300 rounded-lg px-2 py-1 w-full focus:ring-emerald-500 focus:border-emerald-500"
                                                            placeholder="Item description"
                                                            wire:model.defer="form.items.{{ $index }}.description">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>



                        <div class="bg-white p-4 rounded-xl shadow border border-gray-200 mt-6">
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <!-- Date Receipt (Advance Copy) -->
                                <x-forms.date id="date_receipt_advance" label="Date Receipt|Advance Copy"
                                    model="form.date_receipt_advance" :form="$form" :viewOnly="$viewOnlyTab1"
                                    :required="false" colspan="col-span-1" />
                                <!-- Date Receipt (Signed Copy) -->
                                <x-forms.date id="date_receipt_signed" label="Date Receipt|Signed Copy"
                                    model="form.date_receipt_signed" :form="$form" :viewOnly="$viewOnlyTab1"
                                    :required="false" colspan="col-span-1" />
                                <!-- Category -->
                                <x-forms.select id="category_id" label="Category" model="form.category_id" :form="$form"
                                    :options="$categories" optionValue="id" optionLabel="category" :required="true"
                                    :viewOnly="$viewOnlyTab1" wireModifier="lazy" colspan="col-span-1" />
                                <!-- Category Type (Read-only) -->
                                <x-forms.readonly-input id="category_type" label="Category Type"
                                    model="form.category_type" :form="$form" :viewOnly="$viewOnlyTab1" :required="false"
                                    :colspan="1" />
                                <!-- RBAC / SBAC (Read-only) -->
                                <x-forms.readonly-input id="rbac_sbac" label="RBAC / SBAC" model="form.rbac_sbac"
                                    :form="$form" :viewOnly="$viewOnlyTab1" :required="false" :colspan="1" />
                                <!-- DTRACK Number -->
                                <x-forms.input id="dtrack_no" label="DTRACK #" model="form.dtrack_no" :form="$form"
                                    :required="true" :viewOnly="$viewOnlyTab1" colspan="col-span-1" />
                                <!-- UniCode -->
                                <x-forms.input id="unicode" label="UniCode" model="form.unicode" :form="$form"
                                    :required="false" :viewOnly="$viewOnlyTab1" />
                                <!-- Division -->
                                <x-forms.select id="divisions_id" label="Division" model="form.divisions_id"
                                    :form="$form" :options="$divisions" optionValue="id" optionLabel="divisions"
                                    :required="true" :viewOnly="$viewOnlyTab1" colspan="col-span-1" />
                                <!-- Cluster / Committee -->
                                <x-forms.select id="cluster_committees_id" label="Cluster / Committee"
                                    model="form.cluster_committees_id" :form="$form" :options="$clusterCommittees"
                                    optionValue="id" optionLabel="clustercommittee" :required="true"
                                    :viewOnly="$viewOnlyTab1" colspan="col-span-1" />

                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                            <!-- Simple Form Fields in Landscape Layout -->
                            <div class="grid grid-cols-4 gap-4">
                                <!-- Venue Specific -->
                                <x-forms.select id="venue_specific_id" label="Venue|Specific"
                                    model="form.venue_specific_id" :form="$form" :options="$venueSpecifics"
                                    optionValue="id" optionLabel="name" :required="false" :viewOnly="$viewOnlyTab1"
                                    colspan="col-span-1" />
                                <!-- Venue Province/HUC -->
                                <x-forms.select id="venue_province_huc_id" label="Venue Province/HUC"
                                    model="form.venue_province_huc_id" :form="$form" :options="$venueProvinces"
                                    optionValue="id" optionLabel="province_huc" :required="false"
                                    :viewOnly="$viewOnlyTab1" colspan="col-span-1" />
                                <!-- Category / Venue (Read-only) -->
                                <x-forms.readonly-input id="category_venue" label="Category / Venue"
                                    model="form.category_venue" :form="$form" :viewOnly="$viewOnlyTab1"
                                    :required="false" colspan="col-span-2" />
                                <!-- Approved PPMP -->
                                <div class="flex flex-col col-span-2">
                                    <x-forms.approved-ppmp :view-only="$viewOnlyTab1" :form="$form"
                                        model="form.approved_ppmp" othersModel="otherPPMP" />
                                </div>
                                <div class="flex flex-col col-span-2">
                                    <!-- APP Updated -->
                                    <x-forms.app-updated :view-only="$viewOnlyTab1" :form="$form"
                                        model="form.app_updated" othersModel="otherAPP" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                            <div class="grid grid-cols-4 gap-4">
                                <!-- LEFT COLUMN -->
                                <div class="col-span-3 flex gap-4">
                                    <!-- Immediate Date Needed -->
                                    <div class="flex-1">
                                        <x-forms.textarea id="immediate_date_needed" label="Immediate Date Needed"
                                            model="form.immediate_date_needed" :form="$form" :maxlength="500" rows="4"
                                            :viewOnly="$viewOnlyTab1" />

                                    </div>

                                    <!-- Date Needed -->
                                    <div class="flex-1">
                                        <x-forms.textarea id="date_needed" label="Date Needed" model="form.date_needed"
                                            :form="$form" :required="false" :viewOnly="$viewOnlyTab1" :maxlength="500"
                                            rows="4" />
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-span-1 flex flex-col gap-4">
                                    <!-- PMO/End-User -->
                                    <div>
                                        <x-forms.select id="end_users_id" label="PMO/End-User" model="form.end_users_id"
                                            :form="$form" :options="$endUsers" optionValue="id" optionLabel="endusers"
                                            :required="false" :viewOnly="$viewOnlyTab1" />

                                    </div>
                                    <!-- Early Procurement Toggle -->
                                    <div>
                                        <x-forms.early-procurement model="form.early_procurement" :form="$form"
                                            :viewOnly="$viewOnlyTab1" :clickable="false" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="flex justify-center gap-4 mt-6">
                            <!-- Third Box -->
                            <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                                <!-- Simple Form Fields in Landscape Layout -->
                                <div class="grid grid-cols-4 gap-4">
                                    <!-- Source of Funds -->
                                    <div class="col-span-1">
                                        <x-forms.select id="fund_source_id" label="Source of Funds"
                                            model="form.fund_source_id" :form="$form" :options="$fundSources"
                                            optionValue="id" optionLabel="fundsources" :required="true"
                                            :viewOnly="$viewOnlyTab1" />
                                    </div>

                                    <!-- Expense Class -->
                                    <div class="col-span-1">
                                        <x-forms.input id="expense_class" label="Expense Class"
                                            model="form.expense_class" :form="$form" :required="false"
                                            :viewOnly="$viewOnlyTab1" textAlign="right" />
                                    </div>

                                    <!-- ABC Amount -->
                                    <x-forms.currency-input id="abc" label="ABC Amount" model="form.abc" :form="$form"
                                        :required="true" :viewOnly="$viewOnlyTab1" colspan="col-span-1"
                                        wireModifier="live" />


                                    <!-- ABC ⇔ 50k -->
                                    <div class="col-span-1">
                                        <x-forms.abc50k id="abc_50k" label="ABC ⇔ 50k" model="form.abc_50k"
                                            :form="$form" :viewOnly="$viewOnlyTab1" />

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- TAB 2 --}}
                    <div id="card-type-tab-2" class="{{ $activeTab === 2 ? '' : 'hidden' }} mb-4" role="tabpanel"
                        aria-labelledby="card-type-tab-item-2">

                        {{-- Add Mode Button (same gating logic) --}}
                        @php
                        $hasDefaultMode = collect($form['modes'])->contains('mode_of_procurement_id', 1);
                        $hasPendingOrEmptySchedule = collect($form['modes'])->contains(function ($mode) {
                        $schedules = collect($mode['bid_schedules'] ?? []);
                        return $schedules->isEmpty() ||
                        $schedules->contains(fn($s) => empty($s['bidding_result']) && empty($s['ntf_bidding_result']));
                        });
                        @endphp

                        @if (!$viewOnlyTab2 && !$hasDefaultMode && !$hasPendingOrEmptySchedule)
                        <div class="flex justify-end mb-4">
                            <button type="button" wire:click.prevent="addMode"
                                class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="M12 5v14" />
                                </svg>
                                Mode
                            </button>
                        </div>
                        @endif

                        {{-- Modes --}}
                        <div class="space-y-6">
                            @foreach (collect($form['modes'])->values() as $modeIndex => $mode)
                            @php
                            $isModeLocked = !$isEditing &&
                            collect($mode['bid_schedules'] ?? [])->contains(
                            fn($s) => !empty($s['bidding_result']) || !empty($s['ntf_bidding_result'])
                            );
                            $schedules = $mode['bid_schedules'] ?? [];
                            @endphp

                            <div class="mb-8 bg-white p-6 rounded-xl shadow border border-emerald-600">
                                {{-- Header row --}}
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        {{-- Collapse toggle --}}
                                        <button type="button" wire:click="toggleBids({{ $mode['mode_order'] ?? 0 }})"
                                            class="transition p-1 rounded-full border border-gray-300 hover:bg-gray-100">
                                            @if (!($mode['showBids'] ?? false))
                                            {{-- Expand icon --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                            @else
                                            {{-- Collapse icon --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                            @endif
                                        </button>

                                        <label class="font-semibold text-gray-700">Mode of Procurement:</label>
                                        <x-forms.select id="mode_of_procurement_{{ $modeIndex }}" label=""
                                            model="form.modes.{{ $modeIndex }}.mode_of_procurement_id" :form="$form"
                                            :options="$modeOfProcurements" optionValue="id"
                                            optionLabel="modeofprocurements" :required="false"
                                            :viewOnly="$viewOnlyTab2 || $isModeLocked" wireModifier="defer" />
                                    </div>
                                </div>

                                {{-- Collapsible content --}}
                                @if (!empty($mode['showBids']) && $mode['showBids'] === true)

                                {{-- Add Bid Button --}}
                                @if (!in_array($mode['mode_of_procurement_id'], [null, '', 1, 5]))
                                @php
                                $latestModeOrder = collect($form['modes'])->max('mode_order');
                                $isLatestMode = ($mode['mode_order'] ?? null) === $latestModeOrder;
                                $hasMissingBiddingResult = collect($schedules)->contains(
                                fn($s) => empty($s['bidding_result']) && empty($s['ntf_bidding_result'])
                                );
                                $bidCount = count($schedules);
                                $showAddBid = !$viewOnlyTab2 &&
                                $isLatestMode &&
                                !$hasMissingBiddingResult &&
                                ($mode['mode_of_procurement_id'] != 2 || $bidCount < 2); @endphp @if ($showAddBid) <div
                                    class="flex justify-end mb-2">
                                    <button type="button" wire:click.prevent="addBidSchedule({{ $modeIndex }})"
                                        class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14" />
                                            <path d="M12 5v14" />
                                        </svg>
                                        Bid
                                    </button>
                            </div>
                            @endif
                            @endif

                            {{-- =========================
                            TABLE LAYOUT: BID SCHEDULES
                            ========================= --}}
                            @if (!empty($schedules))
                            @php
                            $modeId = $mode['mode_of_procurement_id'];
                            @endphp

                            {{-- CASE A: mode != 4 and mode != 5 --}}
                            @if (!in_array($modeId, [4,5]))
                            <div class="overflow-x-auto">
                                <table class="min-w-[1500px] divide-y divide-gray-200 rounded-xl">
                                    <thead class="bg-gray-50 sticky top-0 z-40">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Bidding #</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                IB No.</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Pre-Proc Conference</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Ads/Post IB</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Pre-Bid Conference</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Eligibility Check</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Sub/Open of Bids</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Bidding Date</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Bidding Result</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($schedules as $bidIndex => $schedule)
                                        @php
                                        $isScheduleLocked = !$isEditing &&
                                        (!empty($schedule['bidding_result']) ||
                                        !empty($schedule['ntf_bidding_result']));
                                        $disabled = $viewOnlyTab2 || $isScheduleLocked;
                                        @endphp
                                        <tr>
                                            {{-- Bidding # --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text" maxlength="2"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-20 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="#"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_number"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- IB No. --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-24 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="IB No."
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ib_number"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Pre-Proc Conference --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_proc_conference"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Ads/Post IB --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ads_post_ib"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Pre-Bid Conference --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_bid_conf"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Eligibility Check --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.eligibility_check"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Sub/Open of Bids --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.sub_open_bids"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Bidding Date --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_date"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- Bidding Result --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <select
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_result"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                                    <option value="">Select</option>
                                                    <option value="SUCCESSFUL">SUCCESSFUL</option>
                                                    <option value="UNSUCCESSFUL">UNSUCCESSFUL</option>
                                                </select>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            {{-- CASE B: mode == 4 (includes Bidding #..Sub/Open + NTF + RFQ/Canvass fields) --}}
                            @if ($modeId == 4)
                            <div class="overflow-x-auto">
                                <table class="min-w-[1800px] divide-y divide-gray-200 rounded-xl">
                                    <thead class="bg-gray-50 sticky top-0 z-40">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Bidding #</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                IB No.</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Pre-Proc Conference</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Ads/Post IB</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Pre-Bid Conference</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Eligibility Check</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Sub/Open of Bids</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                NTF Bidding Date</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                NTF No.</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                NTF Bidding Result</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                RFQ No.</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Canvass Date</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Returned of Canvass</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Abstract of Canvass</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($schedules as $bidIndex => $schedule)
                                        @php
                                        $isScheduleLocked = !$isEditing &&
                                        (!empty($schedule['bidding_result']) ||
                                        !empty($schedule['ntf_bidding_result']));
                                        $disabled = $viewOnlyTab2 || $isScheduleLocked;
                                        @endphp
                                        <tr>
                                            {{-- Bidding # --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text" maxlength="2"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-20 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="#"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_number"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- IB No. --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-24 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="IB No."
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ib_number"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Pre-Proc Conference --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_proc_conference"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Ads/Post IB --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ads_post_ib"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Pre-Bid Conference --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_bid_conf"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Eligibility Check --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.eligibility_check"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Sub/Open of Bids --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.sub_open_bids"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>

                                            {{-- NTF Bidding Date --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntf_bidding_date"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- NTF No. --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-28 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="NTF No."
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntf_no"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- NTF Bidding Result --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <select
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntf_bidding_result"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                                    <option value="">Select</option>
                                                    <option value="SUCCESSFUL">SUCCESSFUL</option>
                                                    <option value="UNSUCCESSFUL">UNSUCCESSFUL</option>
                                                </select>
                                            </td>

                                            {{-- RFQ No. --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-28 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="RFQ No."
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.rfq_no"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Canvass Date --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.canvass_date"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Returned of Canvass --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.date_returned_of_canvass"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Abstract of Canvass --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.abstract_of_canvass_date"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            {{-- CASE C: mode == 5 (resolution + RFQ/canvass-only set) --}}
                            @if ($modeId == 5)
                            <div class="overflow-x-auto">
                                <table class="min-w-[1200px] divide-y divide-gray-200 rounded-xl">
                                    <thead class="bg-gray-50 sticky top-0 z-40">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Resolution Number</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                RFQ No.</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Canvass Date</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Returned of Canvass</th>
                                            <th
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                                Abstract of Canvass</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($schedules as $bidIndex => $schedule)
                                        @php
                                        $isScheduleLocked = !$isEditing &&
                                        (!empty($schedule['bidding_result']) ||
                                        !empty($schedule['ntf_bidding_result']));
                                        $disabled = $viewOnlyTab2 || $isScheduleLocked;
                                        @endphp
                                        <tr>
                                            {{-- Resolution Number --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="Resolution #"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.resolution_number"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- RFQ No. --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500 text-right"
                                                    placeholder="RFQ No."
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.rfq_no"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Canvass Date --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-36 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.canvass_date"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Returned of Canvass --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.date_returned_of_canvass"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                            {{-- Abstract of Canvass --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800">
                                                <input type="date"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-40 focus:ring-emerald-500 focus:border-emerald-500"
                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.abstract_of_canvass_date"
                                                    {{ $disabled ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                            @endif
                            {{-- /TABLE LAYOUT --}}
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- TAB 3 --}}
                <div id="card-type-tab-3" class="{{ $activeTab === 3 ? '' : 'hidden' }} mb-4" role="tabpanel"
                    aria-labelledby="card-type-tab-item-3">
                    {{-- Block 1 --}}
                    <div class="justify-center gap-4 mt-6">
                        <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                            <div class="grid grid-cols-6 gap-4">
                                {{-- Resolution Number --}}
                                <div class="col-span-1">
                                    <x-forms.input id="resolutionNumber" label="Resolution Number"
                                        model="form.resolutionNumber" :form="$form" :required="false"
                                        :viewOnly="$viewOnlyTab3" textAlign="right" />
                                </div>
                                {{-- Bid Evaluation Date --}}
                                <x-forms.date id="bidEvaluationDate" label="Bid Evaluation Date"
                                    model="form.bidEvaluationDate" :form="$form" :viewOnly="$viewOnlyTab3"
                                    :required="false" textAlign="center" />


                                {{-- Post Qual Date --}}
                                <div class="col-span-1">
                                    <x-forms.date id="postQualDate" label="Post Qual Date" model="form.postQualDate"
                                        :form="$form" :viewOnly="$viewOnlyTab3" :required="false" />
                                </div>

                                {{-- Recommending for Award --}}
                                <div class="col-span-1">
                                    <x-forms.date id="recommendingForAward" label="Recommending for Award"
                                        model="form.recommendingForAward" :form="$form" :viewOnly="$viewOnlyTab3"
                                        :required="false" />
                                </div>

                                {{-- Notice of Award --}}
                                <div class="col-span-1">
                                    <x-forms.date id="noticeOfAward" label="Notice of Award" model="form.noticeOfAward"
                                        :form="$form" :viewOnly="$viewOnlyTab3" :required="false" />
                                </div>
                                {{-- Awarded Amount --}}
                                <div class="col-span-1">
                                    <x-forms.currency-input id="awardedAmount" label="Awarded Amount"
                                        model="form.awardedAmount" :form="$form" :required="false"
                                        :viewOnly="$viewOnlyTab3" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Block 2 --}}
                    <div class="justify-center gap-4 mt-6">
                        <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                            <div class="grid grid-cols-6 gap-4">
                                {{-- PhilGEPS Posting Ref --}}
                                <div class="col-span-1">
                                    <x-forms.input id="philgepsReferenceNo" label="PhilGEPS Posting Reference #"
                                        model="form.philgepsReferenceNo" :form="$form" :required="false"
                                        :viewOnly="$viewOnlyTab3" textAlign="right" />
                                </div>

                                {{-- Award Notice Number --}}
                                <div class="col-span-1">
                                    <x-forms.input id="awardNoticeNumber" label="Award Notice Number"
                                        model="form.awardNoticeNumber" :form="$form" :required="false"
                                        :viewOnly="$viewOnlyTab3" textAlign="right" />
                                </div>

                                {{-- Posting of Award on PhilGEPS --}}
                                <div class="col-span-1">
                                    <x-forms.date id="dateOfPostingOfAwardOnPhilGEPS" label="Posting of Award|PhilGEPS"
                                        model="form.dateOfPostingOfAwardOnPhilGEPS" :form="$form"
                                        :viewOnly="$viewOnlyTab3" :required="false" />

                                </div>
                                {{-- Supplier --}}
                                <div class="col-span-1">
                                    <x-forms.select id="supplier_id" label="Supplier" model="form.supplier_id"
                                        :form="$form" :options="$suppliers" optionValue="id" optionLabel="name"
                                        :required="false" :viewOnly="$viewOnlyTab3" />
                                </div>

                                {{-- Process Stage --}}
                                <div class="col-span-1">
                                    <x-forms.select id="procurement_stage_id" label="Procurement Stage"
                                        model="form.procurement_stage_id" :form="$form" :options="$procurementStages"
                                        optionValue="id" optionLabel="procurementstage" :required="false"
                                        :viewOnly="$viewOnlyTab3" />
                                </div>

                                {{-- Remarks --}}
                                <div class="col-span-1">
                                    <x-forms.select id="remarks_id" label="Remarks" model="form.remarks_id"
                                        :form="$form" :options="$remarks" optionValue="id" optionLabel="remarks"
                                        :required="false" :viewOnly="$viewOnlyTab3" />
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="flex justify-end gap-2 p-4 border-t border-gray-200 dark:border-neutral-700">
            <button wire:click="$set('showCreateModal', false)"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-neutral-700 dark:text-white dark:border-neutral-600 dark:hover:bg-neutral-600">
                Cancel
            </button>
            @if (!$viewOnly)
            <button wire:click="saveTabData"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save
            </button>

            @endif

        </div>
    </div>
    {{-- Advance Procurement Prompt --}}


</div>
