<div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-50">
    <div class="relative w-[95%] max-w-6xl h-[95%] bg-white rounded-lg shadow-md dark:bg-neutral-800">
        <!-- Close Button (X) -->
        <button wire:click="closeCreateModal"
            class="absolute top-4 right-4 text-white hover:text-red-800 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-300 px-6 bg-emerald-700 text-white rounded-t-lg">
            <nav class="flex space-x-4 py-4" aria-label="Tabs">
                <button type="button"
                    class="py-2 px-4 text-sm font-medium border-b-2 border-transparent hover:border-white"
                    wire:click="switchTab(1)">
                    Procurement Information
                </button>
                <button type="button"
                    class="py-2 px-4 text-sm font-medium border-b-2 border-transparent hover:border-white"
                    wire:click="switchTab(2)">
                    Additional Details
                </button>
                <button type="button"
                    class="py-2 px-4 text-sm font-medium border-b-2 border-transparent hover:border-white"
                    wire:click="switchTab(3)">
                    Finance
                </button>
            </nav>
        </div>


        <div class="max-h-[80vh] overflow-y-auto p-4">
            <div id="tab-1" role="tabpanel" aria-labelledby="tab-1" class="{{ $activeTab === 1 ? '' : 'hidden' }} ">
                <div class=" max-h-screen">
                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">

                        <!-- Simple Form Fields in Landscape Layout -->
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                            <div class="flex flex-col col-span-1">
                                <label for="pr_number" class="block text-sm font-medium text-gray-700">PR No.</label>
                                <input type="text" id="pr_number" wire:model.defer="form.pr_number" maxlength="8"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-right"
                                    required>
                            </div>

                            <div class="flex flex-col col-span-full">
                                <label for="procurement_program_project"
                                    class="block text-sm font-medium text-gray-700">Procurement Program /
                                    Project</label>
                                <textarea id="procurement_program_project"
                                    wire:model.defer="form.procurement_program_project" maxlength="255"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                                    required></textarea>
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
                                <input type="date" id="date_receipt_signed" wire:model.defer="form.date_receipt_signed"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                                    placeholder="MM/DD/YYYY">
                            </div>

                            <!-- RBAC / SBAC -->
                            <div class="flex flex-col">
                                <label for="rbac_sbac" class="block text-sm font-medium text-gray-700"> </br>RBAC /
                                    SBAC</label>
                                <select id="rbac_sbac" wire:model.defer="form.rbac_sbac"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="RBAC">RBAC</option>
                                    <option value="SBAC">SBAC</option>
                                </select>
                            </div>

                            <!-- DTRACK Number -->
                            <div class="flex flex-col">
                                <label for="dtrack_no" class="block text-sm font-medium text-gray-700"> </br>DTRACK
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
                                <label for="divisions_id"
                                    class="block text-sm font-medium text-gray-700">Division</label>
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
                                <label for="cluster_committees_id"
                                    class="block text-sm font-medium text-gray-700">Cluster /
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
                                <label for="category_id"
                                    class="block text-sm font-medium text-gray-700">Category</label>
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
                                <label for="venue_specific_id"
                                    class="block text-sm font-medium text-gray-700">Venue(Specific)</label>
                                <select id="venue_specific_id" wire:model.defer="form.venue_specific_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    @foreach ($venueSpecifics as $venueSpecific)
                                    <option value="{{ $venueSpecific->id }}">
                                        {{ $venueSpecific->venue }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Venue Province/HUC -->
                            <div class="flex flex-col col-span-2">
                                <label for="category_venue_id" class="block text-sm font-medium text-gray-700">Venue
                                    Province/HUC</label>
                                <select id="category_venue_id" wire:model.defer="form.category_venue_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <!-- Populate with options from your database -->
                                </select>
                            </div>

                            <!-- Category / Venue -->
                            <div class="flex flex-col">
                                <label for="category_venue_id" class="block text-sm font-medium text-gray-700">Category
                                    /
                                    Venue></label>
                                <select id="category_venue_id" wire:model.defer="form.category_venue_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="">Select</option>
                                    @foreach ($categoryVenues as $categoryVenue)
                                    <option value="{{ $categoryVenue->id }}">
                                        {{ $categoryVenue->category_venue }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Approved PPMP -->
                            <div class="flex flex-col col-span-2">
                                <label for="approved_ppmp" class="block text-sm font-medium text-gray-700">w/Approved
                                    PPMP</label>
                                <textarea id="approved_ppmp" wire:model.defer="form.approved_ppmp"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>

                            <!-- APP Updated -->
                            <div class="flex flex-col col-span-2">
                                <label for="app_updated" class="block text-sm font-medium text-gray-700">APP
                                    (Updated)</label>
                                <textarea id="app_updated" wire:model.defer="form.app_updated"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
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
                                <select id="end_users_id" wire:model.defer="form.end_users_id"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <!-- Populate with options from your database -->
                                </select>
                            </div>
                            <!-- Early Procurement Toggle -->

                            <div class="flex items-center gap-x-3">
                                <label for="hs-small-soft-switch-with-icons"
                                    class="relative inline-block w-11 h-6 cursor-pointer">
                                    <input type="checkbox" id="hs-small-soft-switch-with-icons" class="peer sr-only">
                                    <span
                                        class="absolute inset-0 bg-red-200 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-blue-100 dark:bg-neutral-700 dark:peer-checked:bg-blue-800/50 peer-disabled:opacity-50 peer-disabled:pointer-events-none"></span>
                                    <span
                                        class="absolute top-1/2 start-0.5 -translate-y-1/2 size-5 bg-red-600 rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:bg-green-600 peer-checked:translate-x-full dark:bg-neutral-400 dark:peer-checked:bg-green-500"></span>
                                    <!-- Left Icon (Off) -->
                                    <span
                                        class="absolute top-1/2 start-0.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-green-600 transition-colors duration-200 dark:text-neutral-800 dark:peer-checked:text-white">
                                        <svg class="shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18"></path>
                                            <path d="m6 6 12 12"></path>
                                        </svg>
                                    </span>
                                    <!-- Right Icon (On) -->
                                    <span
                                        class="absolute top-1/2 end-0.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-white transition-colors duration-200 dark:text-white">
                                        <svg class="shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </span>
                                </label>
                                <label for="hs-basic-with-description-checked"
                                    class="text-sm text-gray-500 dark:text-neutral-400">Early
                                    Procurement</label>
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
                                    <!-- Populate with options from your database -->
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
                            <div class="flex flex-col">
                                <label for="abc" class="block text-sm font-medium text-gray-700">ABC
                                    Amount</label>
                                <input type="text" id="abc" wire:model.defer="form.abc"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-right" />
                            </div>

                            <!-- ABC 50k -->
                            <div class="flex flex-col">
                                <label for="abc_50k" class="block text-sm font-medium text-gray-700">ABC <=>
                                        50k</label>
                                <select id="rbac_sbac" wire:model.defer="form.rbac_sbac"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                                    <option value="50k_or_less">50k or less</option>
                                    <option value="above_50k">above 50k</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="col-span-1 md:col-span-2 flex justify-end mb-8">
                        <button wire:click="saveTabData"
                            class="mt-4 px-4 py-2 bg-emerald-600  hover:bg-emerald-700 focus:outline-hidden focus:bg-emerald-700 text-white rounded">
                            Save
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <div class="max-h-[80vh] overflow-y-auto p-4">
            <div id="tab-2" role="tabpanel" aria-labelledby="tab-1" class="{{ $activeTab === 2 ? '' : 'hidden' }}">
                <div class="max-h-screen">
                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
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
                            <input type="date" id="pre_proc_conference" wire:model.defer="form.pre_proc_conference"
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

                <div class="col-span-2 mb-8">
                    <button wire:click="saveTabData" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                        Save
                    </button>
                </div>
            </div>
        </div>


    </div>
</div>
</div>