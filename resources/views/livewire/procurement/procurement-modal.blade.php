<div class="fixed inset-0 z-50 flex items-center justify-center bg-emerald-600/20 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg w-full max-w-7xl mx-4 sm:mx-auto transition-all overflow-hidden max-h-[90vh]">

        <!-- Header -->
        <div class="flex justify-between items-center p-1 border-gray-200 bg-emerald-600 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-white ml-2">Procurement</h2>
            <button wire:click="$set('showCreateModal', false)"
                class="text-red-600 hover:text-red-700 dark:text-white dark:hover:text-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
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
                $canAccessTab3 = !empty($procID) && ($hasSuccessfulBidOrNtf || $hasMode5);
            @endphp



            <div class="flex justify-center w-full border-b border-emerald-500">
                <ul class="flex items-center w-full max-w-7xl py-2 bg-white dark:bg-neutral-800 dark:border-neutral-700 mx-48"
                    data-hs-stepper='{"isCompleted": true}'>

                    <!-- Step 1 -->
                    <li class="flex items-center gap-x-2 basis-1/3 group"
                        data-hs-stepper-nav-item='{"index": 1, "isCompleted": {{ $activeTab > 1 ? 'true' : 'false' }} }'>
                        <button type="button" wire:click="switchTab(1)"
                            class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 1 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 1 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                            1
                        </button>
                        <span class="text-sm font-medium text-gray-800 dark:text-white whitespace-nowrap">
                            Purchase Request
                        </span>
                        <!-- Dynamic Line -->
                        <div
                            class="h-px w-48 ml-3 mr-3 transition-colors duration-300
            {{ $activeTab > 1 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-700' }}">
                        </div>
                    </li>

                    <!-- Step 2 -->
                    <li class="flex items-center gap-x-2 basis-1/3 group"
                        data-hs-stepper-nav-item='{"index": 2, "isCompleted": {{ $activeTab > 2 ? 'true' : 'false' }} }'>
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
                        <div
                            class="h-px w-48 ml-3 mr-3 transition-colors duration-300
            {{ $activeTab > 2 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-700' }}">
                        </div>
                    </li>

                    <!-- Step 3 -->
                    <li class="flex items-center gap-x-2 basis-1/3 group"
                        data-hs-stepper-nav-item='{"index": 3, "isCompleted": {{ $activeTab > 3 ? 'true' : 'false' }} }'>
                        <button type="button" @if ($canAccessTab3) wire:click="switchTab(3)" @endif
                            class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 3 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($canAccessTab3 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-neutral-400 cursor-not-allowed') }}"
                            @if (!$canAccessTab3) disabled aria-disabled="true" title="You need a successful bid or Mode 5 to access this tab." @endif>
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
                            <x-forms.input id="pr_number" label="PR No." model="form.pr_number" :form="$form"
                                :required="true" :viewOnly="$viewOnly" />
                            <!-- Procurement Program / Project -->
                            <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                                model="form.procurement_program_project" :form="$form" :required="true"
                                :viewOnly="$viewOnly" :maxlength="500" />
                            <!-- Date Receipt (Advance Copy) -->
                            <x-forms.date id="date_receipt_advance" label="Date Receipt (Advance Copy)"
                                model="form.date_receipt_advance" :form="$form" :viewOnly="$viewOnly"
                                :required="false" />
                            <!-- Date Receipt (Signed Copy) -->
                            <x-forms.date id="date_receipt_signed" label="Date Receipt (Signed Copy)"
                                model="form.date_receipt_signed" :form="$form" :viewOnly="$viewOnly"
                                :required="false" />
                            <!-- RBAC / SBAC -->
                            <x-forms.select id="rbac_sbac" label="RBAC / SBAC" model="form.rbac_sbac" :form="$form"
                                :options="['RBAC' => 'RBAC', 'SBAC' => 'SBAC']" :required="true" :viewOnly="$viewOnly" />
                            <!-- DTRACK Number -->
                            <x-forms.input id="dtrack_no" label="DTRACK #" model="form.dtrack_no" :form="$form"
                                :required="true" :viewOnly="$viewOnly" />
                            <!-- UniCode -->
                            <x-forms.input id="unicode" label="UniCode" model="form.unicode" :form="$form"
                                :required="false" :viewOnly="$viewOnly" />
                            <!-- Division -->
                            <x-forms.select id="divisions_id" label="Division" model="form.divisions_id"
                                :form="$form" :options="$divisions" optionValue="id" optionLabel="divisions"
                                :required="true" :viewOnly="$viewOnly" />
                            <!-- Cluster / Committee -->
                            <x-forms.select id="cluster_committees_id" label="Cluster / Committee"
                                model="form.cluster_committees_id" :form="$form" :options="$clusterCommittees"
                                optionValue="id" optionLabel="clustercommittee" :required="true" :viewOnly="$viewOnly"
                                colspan="col-span-2" />
                            <!-- Category -->
                            <x-forms.select id="category_id" label="Category" model="form.category_id"
                                :form="$form" :options="$categories" optionValue="id" optionLabel="category"
                                :required="true" :viewOnly="$viewOnly" colspan="col-span-2" />
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                        <!-- Simple Form Fields in Landscape Layout -->
                        <div class="grid grid-cols-8 gap-4">
                            <div class="flex flex-col col-span-2">
                                <!-- Venue Specific -->
                                <x-forms.select id="venue_specific_id" label="Venue (Specific)"
                                    model="form.venue_specific_id" :form="$form" :options="$venueSpecifics"
                                    optionValue="id" optionLabel="name" :required="false" :viewOnly="$viewOnly"
                                    colspan="col-span-2" />
                            </div>
                            <div class="flex flex-col col-span-2">
                                <!-- Venue Province/HUC -->
                                <x-forms.select id="venue_province_huc_id" label="Venue Province/HUC"
                                    model="form.venue_province_huc_id" :form="$form" :options="$venueProvinces"
                                    optionValue="id" optionLabel="province_huc" :required="false" :viewOnly="$viewOnly"
                                    colspan="col-span-2" />
                            </div>
                            <div class="flex flex-col col-span-4">
                                <!-- Category / Venue (Read-only) -->
                                <x-forms.readonly-input id="category_venue" label="Category / Venue"
                                    model="form.category_venue" :form="$form" :viewOnly="$viewOnly"
                                    :required="false" />

                            </div>

                            <div class="flex flex-col col-span-4">
                                <!-- Approved PPMP -->
                                <x-forms.approved-ppmp :view-only="$viewOnly" :form="$form" model="form.approved_ppmp"
                                    othersModel="otherPPMP" />
                            </div>
                            <div class="flex flex-col col-span-4">
                                <!-- APP Updated -->
                                <x-forms.app-updated :view-only="$viewOnly" :form="$form" model="form.app_updated"
                                    othersModel="otherAPP" />
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
                                        model="form.immediate_date_needed" :form="$form" :required="false"
                                        :viewOnly="$viewOnly" :maxlength="500" rows="4" />
                                </div>

                                <!-- Date Needed -->
                                <div class="flex-1">
                                    <x-forms.textarea id="date_needed" label="Date Needed" model="form.date_needed"
                                        :form="$form" :required="false" :viewOnly="$viewOnly" :maxlength="500"
                                        rows="4" />
                                </div>
                            </div>

                            <!-- RIGHT COLUMN -->
                            <div class="col-span-1 flex flex-col gap-4">
                                <!-- PMO/End-User -->
                                <div>
                                    <x-forms.select id="end_users_id" label="PMO/End-User" model="form.end_users_id"
                                        :form="$form" :options="$endUsers" optionValue="id" optionLabel="endusers"
                                        :required="false" :viewOnly="$viewOnly" />

                                </div>
                                <!-- Early Procurement Toggle -->
                                <div>
                                    <x-forms.early-procurement model="form.early_procurement" :form="$form"
                                        :viewOnly="$viewOnly" />
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
                                        optionValue="id" optionLabel="fundsources" :required="false"
                                        :viewOnly="$viewOnly" />
                                </div>

                                <!-- Expense Class -->
                                <div class="col-span-1">
                                    <x-forms.input id="expense_class" label="Expense Class"
                                        model="form.expense_class" :form="$form" :required="true"
                                        :viewOnly="$viewOnly" />
                                </div>

                                <!-- ABC Amount -->
                                <div class="col-span-1">
                                    <x-forms.currency-input id="abc" label="ABC Amount" model="form.abc"
                                        :form="$form" :required="true" :viewOnly="$viewOnly" />

                                </div>

                                <!-- ABC ⇔ 50k -->
                                <div class="col-span-1">
                                    <x-forms.abc50k id="abc_50k" label="ABC ⇔ 50k" model="form.abc_50k"
                                        :form="$form" :viewOnly="$viewOnly" />

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                {{-- TAB 2 --}}
                <div id="card-type-tab-2" class="{{ $activeTab === 2 ? '' : 'hidden' }} mb-4" role="tabpanel"
                    aria-labelledby="card-type-tab-item-2">
                    {{-- Add Mode Button --}}
                    @if (!$viewOnly)
                        <div class="flex justify-center mt-2">
                            <button type="button" wire:click.prevent="addMode"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl font-medium shadow">
                                + Mode
                            </button>
                        </div>
                    @endif


                    {{-- Loop through Modes --}}
                    <div class="flex flex-col items-center gap-6 mt-4">

                        @foreach (collect($form['modes'])->values() as $modeIndex => $mode)
                            <div class="bg-white p-4 rounded-xl shadow border border-emerald-600 space-y-6">
                                {{-- Centered and auto-sizing Mode Dropdown --}}
                                <div class="flex justify-center">
                                    <div class="bg-white p-4 rounded-xl border border-gray-200 inline-block">
                                        <x-forms.select id="mode_of_procurement_{{ $modeIndex }}"
                                            label="Mode of Procurement"
                                            model="form.modes.{{ $modeIndex }}.mode_of_procurement_id"
                                            :form="$form" :options="$modeOfProcurements" optionValue="id"
                                            optionLabel="modeofprocurements" :required="false" :viewOnly="$viewOnly" />
                                    </div>
                                </div>



                                {{-- Add Bid Button --}}
                                @if (!in_array($mode['mode_of_procurement_id'], [null, '', 1, 5]))
                                    @if ($mode['mode_of_procurement_id'] == 4 || (isset($mode['bid_schedules']) && count($mode['bid_schedules']) < 2))
                                        <div class="flex justify-center">
                                            @if (!$viewOnly)
                                                <button type="button"
                                                    wire:click.prevent="addBidSchedule({{ $modeIndex }})"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl font-medium shadow">
                                                    + Bid
                                                </button>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="space-y-6">
                                        @if (!empty($mode['bid_schedules']))
                                            @foreach ($mode['bid_schedules'] as $bidIndex => $schedule)
                                                <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                                        {{-- Common Fields --}}
                                                        <div>
                                                            <x-forms.input
                                                                id="ib_number_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="IB No."
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ib_number"
                                                                :form="$form" :required="false" :viewOnly="$viewOnly"
                                                                inputAttributes="maxlength=12 class='text-right'" />
                                                        </div>

                                                        <div>
                                                            <x-forms.date
                                                                id="pre_proc_conference_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="Pre-Proc Conference"
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_proc_conference"
                                                                :form="$form" :viewOnly="$viewOnly" />
                                                        </div>

                                                        <div>
                                                            <x-forms.date
                                                                id="ads_post_ib_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="Ads/Post IB"
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ads_post_ib"
                                                                :form="$form" :viewOnly="$viewOnly" />
                                                        </div>

                                                        <div>
                                                            <x-forms.date
                                                                id="pre_bid_conf_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="Pre-Bid Conference"
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_bid_conf"
                                                                :form="$form" :viewOnly="$viewOnly" />
                                                        </div>

                                                        <div>
                                                            <x-forms.date
                                                                id="eligibility_check_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="Eligibility Check"
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.eligibility_check"
                                                                :form="$form" :viewOnly="$viewOnly" />
                                                        </div>

                                                        <div>
                                                            <x-forms.date
                                                                id="sub_open_bids_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="Sub/Open of Bids"
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.sub_open_bids"
                                                                :form="$form" :viewOnly="$viewOnly" />
                                                        </div>

                                                        {{-- Bidding Number --}}
                                                        <div class="col-span-1">
                                                            <x-forms.input
                                                                id="bidding_number_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                label="Bidding No."
                                                                model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_number"
                                                                :form="$form" :viewOnly="$viewOnly"
                                                                inputAttributes="class='text-right bg-gray-100 cursor-not-allowed'" />
                                                        </div>

                                                        {{-- Bidding Date & Result (except mode 4) --}}
                                                        @if ($mode['mode_of_procurement_id'] != 4)
                                                            <div>
                                                                <x-forms.date
                                                                    id="bidding_date_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="Bidding Date"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_date"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.select
                                                                    id="bidding_result_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="Bidding Result" :options="[
                                                                        'SUCCESSFUL' => 'SUCCESSFUL',
                                                                        'UNSUCCESSFUL' => 'UNSUCCESSFUL',
                                                                    ]"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_result"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>
                                                        @endif

                                                        {{-- Fields for mode_id == 4 --}}
                                                        @if ($mode['mode_of_procurement_id'] == 4)
                                                            <div class="col-span-1">
                                                                <x-forms.input
                                                                    id="ntf_no_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="NTF No."
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntf_no"
                                                                    :form="$form" :viewOnly="$viewOnly"
                                                                    inputAttributes="class='text-right bg-gray-100 cursor-not-allowed'" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.date
                                                                    id="ntf_bidding_date_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="NTF Bidding Date"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntf_bidding_date"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.select
                                                                    id="ntf_bidding_result_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="Bidding Result" :options="[
                                                                        'SUCCESSFUL' => 'SUCCESSFUL',
                                                                        'UNSUCCESSFUL' => 'UNSUCCESSFUL',
                                                                    ]"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntf_bidding_result"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.input
                                                                    id="rfq_no_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="RFQ No."
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.rfq_no"
                                                                    :form="$form" :viewOnly="$viewOnly"
                                                                    inputAttributes="class='text-right bg-gray-100 cursor-not-allowed'" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.date
                                                                    id="canvass_date_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="Canvass Date"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.canvass_date"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.date
                                                                    id="date_returned_of_canvass_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="Returned of Canvass"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.date_returned_of_canvass"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <x-forms.date
                                                                    id="abstract_of_canvass_date_{{ $modeIndex }}_{{ $bidIndex }}"
                                                                    label="Abstract of Canvass"
                                                                    model="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.abstract_of_canvass_date"
                                                                    :form="$form" :viewOnly="$viewOnly" />
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- TAB 3 --}}
                <div id="card-type-tab-3" class="{{ $activeTab === 3 ? '' : 'hidden' }} mb-4" role="tabpanel"
                    aria-labelledby="card-type-tab-item-3">
                    <div class="flex justify-center gap-4 mt-6">
                        <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                            <div class="grid grid-cols-4 gap-4">
                                {{-- Bid Evaluation Date --}}
                                <div class="col-span-1">
                                    <x-forms.date id="bidEvaluationDate" label="Bid Evaluation Date"
                                        model="form.bidEvaluationDate" :form="$form" :viewOnly="$viewOnly"
                                        :required="false" />
                                </div>

                                {{-- Post Qual Date --}}
                                <div class="col-span-1">
                                    <x-forms.date id="postQualDate" label="Post Qual Date" model="form.postQualDate"
                                        :form="$form" :viewOnly="$viewOnly" :required="false" />
                                </div>

                                {{-- Resolution Number --}}
                                <div class="col-span-1">
                                    <x-forms.input id="resolutionNumber" label="Resolution Number"
                                        model="form.resolutionNumber" :form="$form" :required="false"
                                        :viewOnly="$viewOnly" />
                                </div>
                                <div class="col-span-1">
                                    <x-forms.date id="recommendingForAward" label="Recommending for Award"
                                        model="form.recommendingForAward" :form="$form" :viewOnly="$viewOnly"
                                        :required="false" />
                                </div>
                                <div class="col-span-1">
                                    <x-forms.date id="noticeOfAward" label="Notice of Award"
                                        model="form.noticeOfAward" :form="$form" :viewOnly="$viewOnly"
                                        :required="false" />
                                </div>
                                <div class="col-span-1">
                                    <x-forms.currency-input id="awardedAmount" label="Awarded Amount"
                                        model="form.awardedAmount" :form="$form" :required="false"
                                        :viewOnly="$viewOnly" />
                                </div>

                                <div class="col-span-">
                                    <x-forms.date id="dateOfPostingOfAwardOnPhilGEPS"
                                        label="Posting of Award on PhilGEPS"
                                        model="form.dateOfPostingOfAwardOnPhilGEPS" :form="$form"
                                        :viewOnly="$viewOnly" :required="false" />
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
                        class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                        {{ $editingId ? 'Update' : 'Save' }}
                    </button>
                @endif

            </div>
        </div>
    </div>
