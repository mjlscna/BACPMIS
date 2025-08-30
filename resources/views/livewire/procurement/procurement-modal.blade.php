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
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <!-- PR Number -->
                                <div class="col-span-1">
                                    <label for="pr_number" class="block text-sm font-medium text-gray-700 mb-1">
                                        PR No.
                                    </label>
                                    <x-forms.readonly-input id="pr_number" model="form.pr_number" :form="$form"
                                        :viewOnly="$viewOnlyTab1" :required="true" :colspan="1" textAlign="right"
                                        class="flex-1" />

                                    {{-- @if (!$viewOnlyTab1 && !$isEditing)
                                    <button type="button" wire:click="refreshPrNumber" wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center text-emerald-600 hover:text-white hover:bg-emerald-600 rounded-md p-1 transition-colors duration-200"
                                        title="Refresh PR No.">

                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0
                                                           0h4.992m-4.993 0 3.181 3.183a8.25 8.25
                                                           0 0 0 13.803-3.7M4.031 9.865a8.25
                                                           8.25 0 0 1 13.803-3.7l3.181
                                                           3.182m0-4.991v4.99" />
                                        </svg>
                                    </button>
                                    @endif --}}
                                </div>


                                <!-- Procurement Program / Project -->
                                <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                                    model="form.procurement_program_project" :form="$form" :required="true"
                                    :viewOnly="$viewOnlyTab1" :maxlength="500" :rows="1" colspan="col-span-4" />
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
                        <!-- Collapsible Mode of Procurement section with arrow/caret button and Add Mode support -->

                        <div class="w-full" x-data="{
        modes: [ { showBids: false, mode: '' } ],
        addMode() { this.modes.push({ showBids: false, mode: '' }); },
        toggleBids(idx) { this.modes[idx].showBids = !this.modes[idx].showBids; }
     }">

                            <div class="flex justify-end mb-4">
                                <button type="button" @click="addMode()"
                                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-hidden focus:bg-emerald-700">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg> Add Mode
                                </button>
                            </div>

                            <!-- SINGLE CARD -->
                            <div class="mb-8 bg-white p-6 rounded-xl shadow border border-emerald-600">
                                <template x-for="(mode, idx) in modes" :key="idx">
                                    <div class="border border-emerald-600 rounded-xl p-4 mb-4">
                                        <div class="flex items-center gap-2 mb-4">
                                            <button type="button" @click="toggleBids(idx)"
                                                class="transition p-1 rounded-full border border-gray-300 hover:bg-gray-100">
                                                <svg x-show="!mode.showBids" xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                                <svg x-show="mode.showBids" xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <label class="font-semibold text-gray-700">Mode of Procurement:</label>
                                            <select x-model="mode.mode"
                                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                                                <option value="">Select</option>
                                                <option value="Public Bidding">Public Bidding</option>
                                                <option value="Shopping">Shopping</option>
                                                <option value="Direct Contracting">Direct Contracting</option>
                                            </select>
                                        </div>

                                        <template x-if="mode.showBids">
                                            <div>
                                                <div class="flex justify-end mb-2">
                                                    <button type="button"
                                                        class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700">
                                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                            width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M5 12h14" />
                                                            <path d="M12 5v14" />
                                                        </svg> Bid
                                                    </button>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table
                                                        class="min-w-[1500px] divide-y divide-gray-200 dark:divide-neutral-700 rounded-xl">
                                                        <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                                                            <tr>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Bidding #</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    IB No.</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Pre-Proc Conference</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Ads/Post IB</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Pre-Bid Conference</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Eligibility Check</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Sub/Open of Bids</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Bidding Date</th>
                                                                <th
                                                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                                                    Bidding Result</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody
                                                            class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                                            <tr>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="text"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-20 focus:ring-emerald-500 focus:border-emerald-500"
                                                                        placeholder="#">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="text"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-24 focus:ring-emerald-500 focus:border-emerald-500"
                                                                        placeholder="IB No.">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="date"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="date"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="date"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="date"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="date"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <input type="date"
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-32 focus:ring-emerald-500 focus:border-emerald-500">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                                    <select
                                                                        class="border border-gray-300 rounded-lg px-2 py-1 w-28 focus:ring-emerald-500 focus:border-emerald-500">
                                                                        <option value="">Select</option>
                                                                        <option value="SUCCESSFUL">SUCCESSFUL</option>
                                                                        <option value="UNSUCCESSFUL">UNSUCCESSFUL
                                                                        </option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
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
                                        <x-forms.date id="noticeOfAward" label="Notice of Award"
                                            model="form.noticeOfAward" :form="$form" :viewOnly="$viewOnlyTab3"
                                            :required="false" />
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
                                        <x-forms.date id="dateOfPostingOfAwardOnPhilGEPS"
                                            label="Posting of Award|PhilGEPS"
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
                                            model="form.procurement_stage_id" :form="$form"
                                            :options="$procurementStages" optionValue="id"
                                            optionLabel="procurementstage" :required="false"
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
