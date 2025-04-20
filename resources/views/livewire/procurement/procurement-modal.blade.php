<div class="fixed inset-0 z-50 flex items-center justify-center bg-emerald-600/20 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg w-full max-w-6xl mx-4 sm:mx-auto transition-all overflow-hidden max-h-[90vh]">

        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-gray-200 bg-emerald-600 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-white">Procurement</h2>
            <button wire:click="$set('showCreateModal', false)"
                class="text-red-600 hover:text-red-700 dark:text-white dark:hover:text-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="relative">
            <!-- Tab Navigation (within modal content area) -->
            <nav class="w-full flex gap-x-1 px-4 pt-2 bg-white border-b border-emerald-500 dark:bg-neutral-800 dark:border-neutral-700"
                aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                <!-- Tab 1 -->
                <button type="button" wire:click="switchTab(1)"
                    class="{{ $activeTab == 1 ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-800' }} py-3 px-4 inline-flex items-center text-sm font-medium text-center border border-gray-200 rounded-t-lg">
                    Procurement Information
                </button>

                <!-- Tab 2 -->
                <button type="button" wire:click="switchTab(2)"
                    class="{{ $activeTab == 2 ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-800' }} py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium text-center border border-gray-200 rounded-t-lg">
                    Additional Details
                </button>

                <!-- Tab 3 -->
                <button type="button" wire:click="switchTab(3)"
                    class="{{ $activeTab == 3 ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-800' }} py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium text-center border border-gray-200 rounded-t-lg">
                    Finance
                </button>
            </nav>

            <!-- Tab Contents inside bordered div with padding and border -->
            <div class="border px-4 py-2 border-gray-200 dark:border-neutral-700 max-h-[65vh] overflow-y-auto">
                <!-- Added px-4 and border here -->
                <div id="card-type-tab-preview" role="tabpanel" aria-labelledby="card-type-tab-item-1"
                    class="{{ $activeTab === 1 ? '' : 'hidden' }} ">
                    <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                            <div class="flex flex-col col-span-1">
                                <label for="pr_number" class="block text-sm font-medium text-gray-700">
                                    <span class="text-red-500 mr-1">*</span>PR No.
                                </label>

                                <input type="text" id="pr_number" wire:model.defer="form.pr_number" maxlength="8"
                                    required
                                    class="mt-1 block w-full px-3 py-2 rounded-md text-right border
                                        @if ($errors->has('form.pr_number')) border-red-500 focus:ring-red-500 focus:border-red-500
                                        @else
                                            border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @endif" />

                                @error('form.pr_number')
                                    <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex flex-col col-span-full">
                                <label for="procurement_program_project"
                                    class="block text-sm font-medium text-gray-700"> <span
                                        class="text-red-500 mr-1">*</span>Procurement Program /
                                    Project</label>
                                <textarea id="procurement_program_project" wire:model.defer="form.procurement_program_project" maxlength="255"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required></textarea>
                            </div>

                            <!-- Date Receipt (Advance Copy) -->
                            <div class="flex flex-col">
                                <label for="date_receipt_advance" class="block text-sm font-medium text-gray-700">Date
                                    Receipt </br> (Advance Copy)</label>
                                <input type="date" id="date_receipt_advance"
                                    wire:model.defer="form.date_receipt_advance"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md "
                                    placeholder="MM/DD/YYYY">
                            </div>

                            <!-- Date Receipt (Signed Copy) -->
                            <div class="flex flex-col">
                                <label for="date_receipt_signed" class="block text-sm font-medium text-gray-700">Date
                                    Receipt </br> (Signed Copy)</label>
                                <input type="date" id="date_receipt_signed"
                                    wire:model.defer="form.date_receipt_signed"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                                    placeholder="MM/DD/YYYY">
                            </div>

                            <!-- RBAC / SBAC -->
                            <div class="flex flex-col">
                                <label for="rbac_sbac" class="block text-sm font-medium text-gray-700"></br> <span
                                        class="text-red-500 mr-1">*</span>RBAC /
                                    SBAC</label>
                                <select id="rbac_sbac" wire:model.defer="form.rbac_sbac"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    <option value="RBAC">RBAC</option>
                                    <option value="SBAC">SBAC</option>
                                </select>
                            </div>

                            <!-- DTRACK Number -->
                            <div class="flex flex-col">
                                <label for="dtrack_no" class="block text-sm font-medium text-gray-700"> </br> <span
                                        class="text-red-500 mr-1">*</span>DTRACK
                                    #</label>
                                <input type="text" id="dtrack_no" wire:model.defer="form.dtrack_no" maxlength="12"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <!-- UniCode -->
                            <div class="flex flex-col col-span-2">
                                <label for="unicode" class="block text-sm font-medium text-gray-700">
                                    </br>UniCode</label>
                                <input type="text" id="unicode" wire:model.defer="form.unicode" maxlength="30"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-right">
                            </div>

                            <!-- Division -->
                            <div class="flex flex-col">
                                <label for="divisions_id" class="block text-sm font-medium text-gray-700"> <span
                                        class="text-red-500 mr-1">*</span>Division</label>
                                <select id="divisions_id" wire:model.defer="form.divisions_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->divisions }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cluster / Committee -->
                            <div class="flex flex-col col-span-2">
                                <label for="cluster_committees_id" class="block text-sm font-medium text-gray-700">
                                    <span class="text-red-500 mr-1">*</span>Cluster
                                    /
                                    Committee</label>
                                <select id="cluster_committees_id" wire:model.defer="form.cluster_committees_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    @foreach ($clusterCommittees as $clusterCommittee)
                                        <option value="{{ $clusterCommittee->id }}">
                                            {{ $clusterCommittee->clustercommittee }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="flex flex-col col-span-2">
                                <label for="category_id" class="block text-sm font-medium text-gray-700"> <span
                                        class="text-red-500 mr-1">*</span>Category</label>
                                <select id="category_id" wire:model.defer="form.category_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    @foreach ($categories as $categorie)
                                        <option value="{{ $categorie->id }}">
                                            {{ $categorie->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                        <!-- Simple Form Fields in Landscape Layout -->
                        <div class="grid grid-cols-4 gap-4">
                            <!-- Venue Specific -->
                            <div class="flex flex-col">
                                <label for="venue_specific_id" class="block text-sm font-medium text-gray-700">Venue
                                    (Specific)</label>
                                <select id="venue_specific_id" wire:model.live="form.venue_specific_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    @foreach ($venueSpecifics as $venueSpecific)
                                        <option value="{{ $venueSpecific->id }}">{{ $venueSpecific->venue }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Venue Province/HUC -->
                            <div class="flex flex-col">
                                <label for="venue_province_huc_id"
                                    class="block text-sm font-medium text-gray-700">Venue Province/HUC</label>
                                <select id="venue_province_huc_id" wire:model.live="form.venue_province_huc_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    @foreach ($venueProvinces as $venueProvince)
                                        <option value="{{ $venueProvince->id }}">{{ $venueProvince->province }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category / Venue (Read-only) -->
                            <div class="flex flex-col col-span-2">
                                <label for="category_venue" class="block text-sm font-medium text-gray-700">Category /
                                    Venue</label>
                                <input type="text" id="category_venue" wire:model="form.category_venue" readonly
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>


                            <!-- Approved PPMP -->
                            <div class="flex flex-col col-span-2">
                                <label for="approved_ppmp"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <span class="text-red-500 mr-1">*</span>Approved PPMP?
                                </label>

                                <!-- Radio buttons for Yes, No, and Others -->
                                <div class="flex gap-x-6">
                                    <!-- Yes -->
                                    <div class="flex items-center">
                                        <input type="radio" id="radio-yes" wire:model="form.approved_ppmp"
                                            value="Yes" name="approved-ppmp-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-yes"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">Yes</label>
                                    </div>

                                    <!-- No -->
                                    <div class="flex items-center">
                                        <input type="radio" id="radio-no" wire:model="form.approved_ppmp"
                                            value="No" name="approved-ppmp-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-no"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">No</label>
                                    </div>

                                    <!-- Others -->
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="radio-others" wire:model.live="form.approved_ppmp"
                                            value="Others" name="approved-ppmp-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-others"
                                            class="text-sm text-gray-500 dark:text-neutral-400">Others</label>

                                        <!-- Show text input only when "others" is selected -->
                                        @if ($form['approved_ppmp'] === 'Others')
                                            <input type="text" wire:model.defer="otherPPMP"
                                                placeholder="Please specify"
                                                class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-md dark:bg-neutral-700 dark:border-neutral-600 dark:text-white" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- APP Updated -->
                            <div class="flex flex-col col-span-2">
                                <label for="app_updated"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2"> <span
                                        class="text-red-500 mr-1">*</span>
                                    APP Updated?
                                </label>
                                <!-- Radio buttons for Yes, No, and Others -->
                                <div class="flex gap-x-6">
                                    <!-- Yes -->
                                    <div class="flex items-center">
                                        <input type="radio" id="radio-yes" wire:model.live="form.app_updated"
                                            value="Yes" name="app-updated-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-yes"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">Yes</label>
                                    </div>
                                    <!-- Others -->
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="radio-others" wire:model.live="form.app_updated"
                                            value="Others" name="app-updated-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500
                       dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500
                       dark:focus:ring-offset-gray-800">
                                        <label for="radio-others"
                                            class="text-sm text-gray-500 dark:text-neutral-400">Others</label>

                                        <!-- Text Input shown when "Others" is selected -->
                                        @if (optional($form)['app_updated'] === 'Others')
                                            <input type="text" wire:model.defer="otherAPP"
                                                placeholder="Please specify"
                                                class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-md dark:bg-neutral-700
                           dark:border-neutral-600 dark:text-white" />
                                        @endif
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                        <!-- Simple Form Fields in Landscape Layout -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Immediate Date Needed -->
                            <div class="flex flex-col">
                                <label for="immediate_date_needed"
                                    class="block text-sm font-medium text-gray-700">Immediate Date Needed</label>
                                <textarea id="immediate_date_needed" wire:model.defer="form.immediate_date_needed"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>

                            <!-- Date Needed -->
                            <div class="flex flex-col">
                                <label for="date_needed" class="block text-sm font-medium text-gray-700">Date
                                    Needed</label>
                                <textarea id="date_needed" wire:model.defer="form.date_needed"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>

                            <!-- PMO/End-User -->
                            <div class="flex flex-col">
                                <label for="end_users_id"
                                    class="block text-sm font-medium text-gray-700">PMO/End-User</label>
                                <select id="end_users_id" wire:model.live="form.end_users_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    @foreach ($endUsers as $endUser)
                                        <option value="{{ $endUser->id }}">
                                            {{ $endUser->endusers }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Early Procurement Toggle -->

                            <div class="flex items-center gap-x-3">
                                <label for="early_procurement" class="block text-sm font-medium text-gray-700"> <span
                                        class="text-red-500 mr-1">*</span>Early
                                    Procurement</label>
                                <label for="hs-large-switch-with-icons"
                                    class="relative inline-block w-15 h-8 cursor-pointer">
                                    <input type="checkbox" id="hs-large-switch-with-icons" class="peer sr-only"
                                        wire:model.defer="form.early_procurement">
                                    <span
                                        class="absolute inset-0 bg-red-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600 dark:bg-neutral-700 dark:peer-checked:bg-blue-500 peer-disabled:opacity-50 peer-disabled:pointer-events-none"></span>
                                    <span
                                        class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full dark:bg-neutral-400 dark:peer-checked:bg-white"></span>
                                    <!-- Left Icon (Off) -->
                                    <span
                                        class="absolute top-1/2 start-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-red-500 peer-checked:text-white transition-colors duration-200 dark:text-neutral-500">
                                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </span>
                                    <!-- Right Icon (On) -->
                                    <span
                                        class="absolute top-1/2 end-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-emerald-600 transition-colors duration-200 dark:text-neutral-500">
                                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>
                                </label>
                            </div>


                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                        <!-- Simple Form Fields in Landscape Layout -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Source of Funds -->
                            <div class="flex flex-col">
                                <label for="fund_source_id" class="block text-sm font-medium text-gray-700">Source of
                                    Funds</label>
                                <select id="fund_source_id" wire:model.defer="form.fund_source_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select</option>
                                    @foreach ($fundSources as $fundSource)
                                        <option value="{{ $fundSource->id }}">
                                            {{ $fundSource->fundsources }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Expense Class -->
                            <div class="flex flex-col">
                                <label for="expense_class" class="block text-sm font-medium text-gray-700">Expense
                                    Class</label>
                                <input type="text" id="expense_class" wire:model.defer="form.expense_class"
                                    maxlength="255"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <!-- ABC Amount -->
                            <div class="flex flex-col" x-data="{ displayABC: '{{ number_format((float) ($form['abc'] ?? 0), 2) }}' }">

                                <label for="abc" class="block text-sm font-medium text-gray-700 pl-5">
                                    <span class="text-red-500 mr-1">*</span>ABC Amount
                                </label>

                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₱</span>

                                    <input type="text" id="abc" x-model="displayABC"
                                        @input="displayABC = $event.target.value.replace(/[^0-9.]/g, '')"
                                        @blur="
                                            let num = parseFloat(displayABC.replace(/,/g, ''));
                                            if (!isNaN(num)) {
                                                displayABC = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
                                                $wire.set('form.abc', num);
                                            } else {
                                                displayABC = '';
                                                $wire.set('form.abc', null);
                                            }
                                        "
                                        class="mt-1 block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md text-right"
                                        inputmode="decimal" />
                                </div>
                            </div>


                            <!-- ABC ⇔ 50k -->
                            <div class="flex flex-col">
                                <label for="abc_50k" class="block text-sm font-medium text-gray-700 pl-5">ABC ⇔
                                    50k</label>
                                <select disabled
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                    <option value="50k_or_less"
                                        {{ $form['abc_50k'] === '50k_or_less' ? 'selected' : '' }}>50k or less</option>
                                    <option value="above_50k"
                                        {{ $form['abc_50k'] === 'above_50k' ? 'selected' : '' }}>above 50k</option>
                                </select>
                            </div>


                        </div>
                    </div>
                </div>
                {{-- TAB 2 --}}
                <div id="card-type-tab-2" class="{{ $activeTab === 2 ? '' : 'hidden' }}" role="tabpanel"
                    aria-labelledby="card-type-tab-item-2">
                    <div class="bg-white p-4 rounded-xl shadow border border-gray-200 ">
                        <div class="flex flex-col mb-4">
                            <label for="mode_of_procurement_id" class="block text-sm font-medium text-gray-700">Mode
                                of
                                Procurement</label>
                            <select id="mode_of_procurement_id" wire:model.defer="form.mode_of_procurement_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required
                                style="width: 300px;">
                                <option value="1" selected>For BAC Decision</option>
                                <option value="2">Competitive Bidding</option>
                            </select>
                        </div>

                        <div class="flex flex-col mb-4">
                            <label for="ib_number" class="block text-sm font-medium text-gray-700">IB No.</label>
                            <input type="text" id="ib_number" wire:model.defer="form.ib_number" maxlength="12"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-right" />
                        </div>

                        <div class="flex flex-col mb-4">
                            <label for="pre_proc_conference" class="block text-sm font-medium text-gray-700">Pre-Proc
                                Conference</label>
                            <input type="date" id="pre_proc_conference"
                                wire:model.defer="form.pre_proc_conference"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                        </div>

                        <div class="flex flex-col mb-4">
                            <label for="ads_post_ib" class="block text-sm font-medium text-gray-700">Ads/Post
                                IB</label>
                            <input type="date" id="ads_post_ib" wire:model.defer="form.ads_post_ib"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                        </div>

                        <div class="flex flex-col mb-4">
                            <label for="pre_bid_conf" class="block text-sm font-medium text-gray-700">Pre-Bid
                                Conference</label>
                            <input type="date" id="pre_bid_conf" wire:model.defer="form.pre_bid_conf"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                        </div>

                        <div class="flex flex-col mb-4">
                            <label for="eligibility_check" class="block text-sm font-medium text-gray-700">Eligibility
                                Check</label>
                            <input type="date" id="eligibility_check" wire:model.defer="form.eligibility_check"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                        </div>

                        <div class="flex flex-col mb-4">
                            <label for="sub_open_bids" class="block text-sm font-medium text-gray-700">Sub/Open of
                                Bids</label>
                            <input type="date" id="sub_open_bids" wire:model.defer="form.sub_open_bids"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                        </div>
                    </div>
                </div>

                <div id="card-type-tab-3" class="{{ $activeTab === 3 ? '' : 'hidden' }}" role="tabpanel"
                    aria-labelledby="card-type-tab-item-3">
                    <p class="text-gray-500 dark:text-neutral-400">
                        This is the <em class="font-semibold text-gray-800 dark:text-neutral-200">third</em> item's tab
                        body.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 p-4 border-t border-gray-200 dark:border-neutral-700">
            <button wire:click="$set('showCreateModal', false)"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-neutral-700 dark:text-white dark:border-neutral-600 dark:hover:bg-neutral-600">
                Cancel
            </button>
            <button wire:click="saveProcurement"
                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                {{ $editingId ? 'Update' : 'Save' }}
            </button>

        </div>
    </div>
</div>
