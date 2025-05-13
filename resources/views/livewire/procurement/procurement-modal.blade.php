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

        <div class="relative ">
            <!-- Tab Navigation (within modal content area) -->
            <!-- Stepper Navigation -->
            <ul class="relative flex justify-center gap-x-2 px-4 py-3 pt-2 bg-white border-b border-emerald-500 dark:bg-neutral-800 dark:border-neutral-700"
                data-hs-stepper='{"isCompleted": true}'>

                <!-- Step 1: PR Details -->
                <li class="flex items-center gap-x-2 shrink basis-0 flex-1 group"
                    data-hs-stepper-nav-item='{"index": 1, "isCompleted": {{ $activeTab > 1 ? 'true' : 'false' }} }'>
                    <button type="button" wire:click="switchTab(1)"
                        class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
                {{ $activeTab == 1 ? 'bg-green-600 text-white' : ($activeTab > 1 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                        @if ($activeTab > 1)
                            <!-- Completed check icon -->
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3"
                                viewBox="0 0 24 24">
                                <path d="M20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @else
                            1
                        @endif
                    </button>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">PR Details</span>
                    <div class="w-full h-px flex-1 bg-gray-200 group-last:hidden dark:bg-neutral-700"></div>
                </li>

                <!-- Step 2: Mode of Procurement -->
                @php
                    $canAccessTab2 = !empty($form['modes'][0]['mode_of_procurement_id'] ?? null) && !empty($procID);
                @endphp
                <li class="flex items-center gap-x-2 shrink basis-0 flex-1 group"
                    data-hs-stepper-nav-item='{"index": 2, "isCompleted": {{ $activeTab > 2 ? 'true' : 'false' }} }'>
                    <button type="button" @if ($canAccessTab2) wire:click="switchTab(2)" @endif
                        class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
                {{ $activeTab == 2 ? 'bg-green-600 text-white' : ($activeTab > 2 ? 'bg-emerald-600 text-white' : ($canAccessTab2 ? 'bg-gray-100 text-gray-800' : 'bg-gray-100 text-neutral-400 cursor-not-allowed')) }}"
                        @if (!$canAccessTab2) disabled @endif>
                        @if ($activeTab > 2)
                            <!-- Completed check icon -->
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3"
                                viewBox="0 0 24 24">
                                <path d="M20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @else
                            2
                        @endif
                    </button>
                    <span
                        class="text-sm font-medium {{ $canAccessTab2 ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                        Mode of Procurement
                    </span>
                    <div class="w-full h-px flex-1 bg-gray-200 group-last:hidden dark:bg-neutral-700"></div>
                </li>

                <!-- Step 3: Post -->
                <li class="flex items-center gap-x-2 shrink basis-0 flex-1 group"
                    data-hs-stepper-nav-item='{"index": 3}'>
                    <button type="button"
                        class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition bg-gray-100 text-neutral-400 cursor-not-allowed"
                        disabled>
                        3
                    </button>
                    <span class="text-sm font-medium text-neutral-400 dark:text-neutral-500">
                        Post
                    </span>
                </li>

            </ul>

            <!-- Tab Contents inside bordered div with padding and border -->
            <div class="border px-4 py-2 border-gray-200 dark:border-neutral-700 max-h-[65vh] overflow-y-auto">
                <!-- PR -->
                <div id="card-type-tab-preview" role="tabpanel" aria-labelledby="card-type-tab-item-1"
                    class="{{ $activeTab === 1 ? '' : 'hidden' }} mb-4 mt-4">
                    <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
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
                                <input type="text" id="dtrack_no" wire:model.defer="form.dtrack_no"
                                    maxlength="12"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <!-- UniCode -->
                            <div class="flex flex-col col-span-1">
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
                        <div class="grid grid-cols-8 gap-4">
                            <!-- Venue Specific -->
                            <div class="flex flex-col col-span-2">
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
                            <div class="flex flex-col col-span-2">
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
                            <div class="flex flex-col col-span-4">
                                <label for="category_venue" class="block text-sm font-medium text-gray-700">Category /
                                    Venue</label>
                                <input type="text" id="category_venue" wire:model="form.category_venue" readonly
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                            </div>


                            <!-- Approved PPMP -->
                            <div class="flex flex-col col-span-4">
                                <label for="approved_ppmp"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Approved
                                    PPMP?
                                </label>

                                <!-- Radio buttons for Yes, No, and Others -->
                                <div class="flex gap-x-6">
                                    <!-- Yes -->
                                    <div class="flex items-center">
                                        <input type="radio" id="radio-yes" wire:model.live="form.approved_ppmp"
                                            value="Yes" name="approved-ppmp-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-yes"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">Yes</label>
                                    </div>

                                    <!-- No -->
                                    <div class="flex items-center">
                                        <input type="radio" id="radio-no" wire:model.live="form.approved_ppmp"
                                            value="No" name="approved-ppmp-group"
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-no"
                                            class="text-sm text-gray-500 ms-2 dark:text-neutral-400">No</label>
                                    </div>

                                    <!-- Others -->
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="radio-others" wire:model.live="form.approved_ppmp"
                                            value="Others" name="approved-ppmp-group"
                                            @if (!in_array($form['approved_ppmp'], ['Yes', 'No'])) checked @endif
                                            class="shrink-0 mt-0.5 border-gray-200 rounded-full text-blue-600 focus:ring-blue-500 checked:border-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500 dark:checked:border-blue-500 dark:focus:ring-offset-gray-800">
                                        <label for="radio-others"
                                            class="text-sm text-gray-500 dark:text-neutral-400">Others</label>

                                        <!-- Show text input only when "others" is selected -->
                                        @if (!in_array($form['approved_ppmp'], ['Yes', 'No']))
                                            <textarea wire:model.defer="otherPPMP" placeholder="Please specify"
                                                class="w-75 px-3 py-1.5 text-sm border border-gray-300 rounded-md dark:bg-neutral-700
               dark:border-neutral-600 dark:text-white"
                                                rows="3"></textarea>
                                        @endif

                                    </div>
                                </div>
                            </div>

                            <!-- APP Updated -->
                            <div class="flex flex-col col-span-4">
                                <label for="app_updated"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    APP Updated?
                                </label>
                                <!-- Radio buttons for Yes, No, and Others -->
                                <div class="flex gap-x-6">
                                    <!-- Yes -->
                                    <div class="flex items-center">
                                        <input type="radio" id="radio-yes" wire:model.live="form.app_updated"
                                            @if (!in_array($form['app_updated'], ['Yes', 'No'])) checked @endif value="Yes"
                                            name="app-updated-group"
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
                                        @if (!in_array($form['app_updated'], ['Yes', 'No']))
                                            <textarea wire:model.defer="otherAPP" placeholder="Please specify"
                                                class="w-75 px-3 py-1.5 text-sm border border-gray-300 rounded-md dark:bg-neutral-700
               dark:border-neutral-600 dark:text-white"
                                                rows="3"></textarea>
                                        @endif

                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
                        <div class="grid grid-cols-4 gap-4">
                            <!-- LEFT COLUMN -->
                            <div class="col-span-3 flex gap-4">
                                <!-- Immediate Date Needed -->
                                <div class="flex-1">
                                    <label for="immediate_date_needed"
                                        class="block text-sm font-medium text-gray-700">Immediate Date Needed</label>
                                    <textarea id="immediate_date_needed" wire:model.defer="form.immediate_date_needed" rows="4"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                                </div>

                                <!-- Date Needed -->
                                <div class="flex-1">
                                    <label for="date_needed" class="block text-sm font-medium text-gray-700">Date
                                        Needed</label>
                                    <textarea id="date_needed" wire:model.defer="form.date_needed" rows="4"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>


                            <!-- RIGHT COLUMN -->
                            <div class="col-span-1 flex flex-col gap-4"">
                                <!-- PMO/End-User -->
                                <div>
                                    <label for="end_users_id"
                                        class="block text-sm font-medium text-gray-700">PMO/End-User</label>
                                    <select id="end_users_id" wire:model.live="form.end_users_id"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="">Select</option>
                                        @foreach ($endUsers as $endUser)
                                            <option value="{{ $endUser->id }}">{{ $endUser->endusers }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Early Procurement Toggle -->
                                <div>
                                    <label for="early_procurement"
                                        class="block text-sm font-medium text-gray-700 mb-1">Early Procurement</label>
                                    <label for="hs-large-switch-with-icons"
                                        class="relative inline-block w-15 h-8 cursor-pointer">
                                        <input type="checkbox" id="hs-large-switch-with-icons" class="peer sr-only"
                                            wire:model.defer="form.early_procurement">
                                        <span
                                            class="absolute inset-0 bg-red-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>
                                        <span
                                            class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full"></span>

                                        <!-- Left Icon -->
                                        <span
                                            class="absolute top-1/2 start-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-red-500 peer-checked:text-white">
                                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 6 6 18"></path>
                                                <path d="m6 6 12 12"></path>
                                            </svg>
                                        </span>

                                        <!-- Right Icon -->
                                        <span
                                            class="absolute top-1/2 end-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-emerald-600">
                                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </span>
                                    </label>
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
                                    <label for="fund_source_id" class="block text-sm font-medium text-gray-700">Source
                                        of Funds</label>
                                    <select id="fund_source_id" wire:model.defer="form.fund_source_id"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="">Select</option>
                                        @foreach ($fundSources as $fundSource)
                                            <option value="{{ $fundSource->id }}">{{ $fundSource->fundsources }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Expense Class -->
                                <div class="col-span-1">
                                    <label for="expense_class" class="block text-sm font-medium text-gray-700">Expense
                                        Class</label>
                                    <input type="text" id="expense_class" wire:model.defer="form.expense_class"
                                        maxlength="255"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <!-- ABC Amount -->
                                <div class="col-span-1" x-data="{ displayABC: '{{ number_format((float) ($form['abc'] ?? 0), 2) }}' }">
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
                                <div class="col-span-1">
                                    <label for="abc_50k" class="block text-sm font-medium text-gray-700 pl-5">ABC ⇔
                                        50k</label>
                                    <select disabled
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                        <option value="50k_or_less"
                                            {{ $form['abc_50k'] === '50k_or_less' ? 'selected' : '' }}>50k or less
                                        </option>
                                        <option value="above_50k"
                                            {{ $form['abc_50k'] === 'above_50k' ? 'selected' : '' }}>above 50k</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                {{-- TAB 2 --}}
                <div id="card-type-tab-2" class="{{ $activeTab === 2 ? '' : 'hidden' }} mb-4" role="tabpanel"
                    aria-labelledby="card-type-tab-item-2">
                    {{-- Add Mode Button --}}
                    <div class="flex justify-center mt-2">
                        <button type="button" wire:click.prevent="addMode"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl font-medium shadow">
                            + Add Mode
                        </button>
                    </div>

                    {{-- Loop through Modes --}}
                    <div class="flex flex-col items-center gap-6 mt-4">

                        @foreach (collect($form['modes'])->values() as $modeIndex => $mode)
                            <div
                                class="bg-white p-4 rounded-xl shadow border border-emerald-600 w-full max-w-5xl space-y-6 mx-auto">
                                {{-- Mode Dropdown --}}
                                <div class="bg-white p-4 rounded-xl border border-gray-200 max-w-md mx-auto">
                                    <label class="block text-sm font-medium text-gray-700 text-center">
                                        Mode of Procurement
                                    </label>
                                    <select wire:model.live="form.modes.{{ $modeIndex }}.mode_of_procurement_id"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-center"
                                        required>
                                        <option value="">Select</option>
                                        @foreach ($modeOfProcurements as $modeOption)
                                            <option value="{{ $modeOption->id }}">
                                                {{ $modeOption->modeofprocurements }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Add Bid Button --}}
                                @if (!in_array($mode['mode_of_procurement_id'], [null, '', 1, 5]))
                                    @if ($mode['mode_of_procurement_id'] == 4 || (isset($mode['bid_schedules']) && count($mode['bid_schedules']) < 2))
                                        <div class="flex justify-center">
                                            <button type="button"
                                                wire:click.prevent="addBidSchedule({{ $modeIndex }})"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl font-medium shadow">
                                                + Add
                                            </button>
                                        </div>
                                    @endif

                                    <div class="space-y-6">
                                        @if (!empty($mode['bid_schedules']))
                                            @foreach ($mode['bid_schedules'] as $bidIndex => $schedule)
                                                <div class="bg-white p-6 rounded-xl shadow border border-gray-200">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                                        {{-- Common Fields --}}
                                                        <div>
                                                            <label class="text-sm font-medium text-gray-700">IB
                                                                No.</label>
                                                            <input type="text"
                                                                wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ib_number"
                                                                maxlength="12"
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-right" />
                                                        </div>

                                                        <div>
                                                            <label class="text-sm font-medium text-gray-700">Pre-Proc
                                                                Conference</label>
                                                            <input type="date"
                                                                wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_proc_conference"
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                        </div>

                                                        <div>
                                                            <label class="text-sm font-medium text-gray-700">Ads/Post
                                                                IB</label>
                                                            <input type="date"
                                                                wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ads_post_ib"
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                        </div>

                                                        <div>
                                                            <label class="text-sm font-medium text-gray-700">Pre-Bid
                                                                Conference</label>
                                                            <input type="date"
                                                                wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.pre_bid_conf"
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                        </div>

                                                        <div>
                                                            <label
                                                                class="text-sm font-medium text-gray-700">Eligibility
                                                                Check</label>
                                                            <input type="date"
                                                                wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.eligibility_check"
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                        </div>

                                                        <div>
                                                            <label class="text-sm font-medium text-gray-700">Sub/Open
                                                                of
                                                                Bids</label>
                                                            <input type="date"
                                                                wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.sub_open_bids"
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                        </div>

                                                        {{-- Bidding Number --}}
                                                        <div class="col-span-1">
                                                            <label class="text-sm font-medium text-gray-700">Bidding
                                                                No.</label>

                                                            {{-- Display for user (readonly) --}}
                                                            <input type="text"
                                                                value="{{ $schedule['bidding_number'] }}" readonly
                                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed text-right">

                                                        </div>

                                                        {{-- Bidding Date & Result (except mode 4) --}}
                                                        @if ($mode['mode_of_procurement_id'] != 4)
                                                            <div>
                                                                <label
                                                                    class="text-sm font-medium text-gray-700">Bidding
                                                                    Date</label>
                                                                <input type="date"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_date"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label
                                                                    class="text-sm font-medium text-gray-700">Bidding
                                                                    Result</label>
                                                                <select
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.bidding_result"
                                                                    class="mt-1 block w-full px-1 py-2 border border-gray-300 rounded-md">
                                                                    <option value="">Select</option>
                                                                    <option value="SUCCESSFUL">SUCCESSFUL</option>
                                                                    <option value="UNSUCCESSFUL">UNSUCCESSFUL</option>
                                                                </select>
                                                            </div>
                                                        @endif

                                                        {{-- Fields for mode_id == 4 --}}
                                                        @if ($mode['mode_of_procurement_id'] == 4)
                                                            <div class="col-span-1">
                                                                <label class="text-sm font-medium text-gray-700">NTF
                                                                    No.</label>
                                                                <input type="text"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntfNumber"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label class="text-sm font-medium text-gray-700">NTF
                                                                    Bidding Date</label>
                                                                <input type="date"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntfBiddingDate"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label class="text-sm font-medium text-gray-700">NTF
                                                                    Bidding Result</label>
                                                                <select
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.ntfBiddingResult"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                                                    <option value="">Select</option>
                                                                    <option value="SUCCESSFUL">SUCCESSFUL</option>
                                                                    <option value="UNSUCCESSFUL">UNSUCCESSFUL</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label class="text-sm font-medium text-gray-700">RFQ
                                                                    No.</label>
                                                                <input type="text"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.rfqNo"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label
                                                                    class="text-sm font-medium text-gray-700">Canvass
                                                                    Date</label>
                                                                <input type="date"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.postQualDate"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label
                                                                    class="text-sm font-medium text-gray-700">Returned
                                                                    of Canvass</label>
                                                                <input type="date"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.dateReturnedOfCanvass"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                                            </div>

                                                            <div class="col-span-1">
                                                                <label
                                                                    class="text-sm font-medium text-gray-700">Abstract
                                                                    of Canvass</label>
                                                                <input type="date"
                                                                    wire:model.defer="form.modes.{{ $modeIndex }}.bid_schedules.{{ $bidIndex }}.abstractOfCanvassDate"
                                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
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
                                    <label class="text-sm font-medium text-gray-700">Bid Evaluation Date</label>
                                    <input type="date" wire:model.defer="form.bidEvaluationDate"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                </div>

                                {{-- Post Qual Date --}}
                                <div class="col-span-1">
                                    <label class="text-sm font-medium text-gray-700">Post Qual Date</label>
                                    <input type="date" wire:model.defer="form.postQualDate"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                </div>

                                {{-- Resolution Number --}}
                                <div class="col-span-1">
                                    <label class="text-sm font-medium text-gray-700">Resolution Number</label>
                                    <input type="text" wire:model.defer="form.resolutionNumber"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                </div>
                                <div class="col-span-1">
                                    <label class="text-sm font-medium text-gray-700">Recommending for Award</label>
                                    <input type="date" wire:model.defer="form.recommendingForAward"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                </div>
                                <div class="col-span-1">
                                    <label class="text-sm font-medium text-gray-700">Notice of Award</label>
                                    <input type="date" wire:model.defer="form.noticeOfAward"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                </div>
                                <div class="col-span-1">
                                    <label class="text-sm font-medium text-gray-700">Awarded Amount</label>
                                    <input type="number" wire:model.defer="form.awardedAmount"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
                                </div>
                                <div class="col-span-">
                                    <label class="text-sm font-medium text-gray-700">Posting of Award on
                                        PhilGEPS</label>
                                    <input type="date" wire:model.defer="form.dateOfPostingOfAwardOnPhilGEPS"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" />
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
                <button wire:click="saveTabData"
                    class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                    {{ $editingId ? 'Update' : 'Save' }}
                </button>

            </div>
        </div>
    </div>
