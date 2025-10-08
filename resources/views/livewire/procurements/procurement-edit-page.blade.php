<div class="space-y-6 p-8 pb-[5rem]">

    {{-- First Box --}}
    <div
        class="bg-white p-4 rounded-xl shadow border border-gray-200
                dark:bg-neutral-700 dark:border-neutral-700 ">
        <!-- Grid for PR No. + Program/Project -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- PR Number -->
            <div class="col-span-1">
                <label for="pr_number" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
                    PR No.
                </label>
                <x-forms.readonly-input id="pr_number" model="form.pr_number" :form="$form" :required="true"
                    :colspan="1" textAlign="right" class="flex-1" />
            </div>

            <!-- Procurement Program / Project -->
            <div class="col-span-4">
                <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                    model="form.procurement_program_project" :required="true" :rows="$textareaRows" :readonly="true" />
            </div>
        </div>

        <!-- Per Lot / Per Item Toggle + Table -->
        <div class="mt-6 flex flex-col md:flex-row md:items-start md:space-x-6">
            <!-- Toggle -->
            <div class="flex items-center gap-x-3">
                <x-forms.prType id="procurement-toggle" model="form.procurement_type" :form="$form"
                    :clickable="false" />
            </div>
            <!-- Table shows only when "Per Item" is selected -->
            @if ($form['procurement_type'] === 'perItem')
                <div class="mt-4 md:mt-0 w-full md:max-w-3xl">
                    {{-- Header row --}}
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-x-2">
                            {{-- Show/Hide table button --}}
                            <button type="button" wire:click="$toggle('showTable')"
                                class="transition p-1 rounded-full hover:bg-gray-100">
                                @if (!$showTable)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            </button>

                        </div>
                    </div>

                    @if ($showTable)
                        <div
                            class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 overflow-x-auto w-full">

                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-700 dark:text-white">Item List</h3>
                                <button type="button" wire:click="addItem"
                                    class="py-1 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                                    <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    Item
                                </button>
                            </div>

                            {{-- Items table component --}}
                            @if ($form['procurement_type'] === 'perItem')
                                <x-forms.prItems-table :form="$form" model="form.items" :page="$page"
                                    :per-page="$perPage" />
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
                colspan="col-span-2" :searchable="false" />
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
            <x-forms.input id="unicode" label="UniCode" model="form.unicode" :form="$form" :required="false"
                colspan="col-span-2" />
            <!-- Division -->
            <x-forms.select id="divisions_id" label="Division" model="form.divisions_id" :form="$form"
                :options="$divisions" optionValue="id" optionLabel="divisions" :required="true" colspan="col-span-3"
                :searchable="false" />
            <!-- Cluster / Committee -->
            <x-forms.select id="cluster_committees_id" label="Cluster / Committee" model="form.cluster_committees_id"
                :form="$form" :options="$clusterCommittees" optionValue="id" optionLabel="clustercommittee" :required="true"
                colspan="col-span-2" :searchable="false" />

        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6 dark:bg-neutral-700 dark:border-neutral-700">
        <!-- Simple Form Fields in Landscape Layout -->
        <div class="grid grid-cols-4 gap-4">
            <!-- Venue Specific -->
            <x-forms.select id="venue_specific_id" label="Venue|Specific" model="form.venue_specific_id"
                :form="$form" :options="$venueSpecifics" optionValue="id" optionLabel="name" :required="false"
                colspan="col-span-2" :searchable="false" />
            <!-- Venue Province/HUC -->
            <x-forms.select id="venue_province_huc_id" label="Venue|Province/HUC" model="form.venue_province_huc_id"
                :form="$form" :options="$venueProvinces" optionValue="id" optionLabel="province_huc" :required="false"
                colspan="col-span-2" :searchable="false" />
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
                        :required="true" />

                </div>

                <!-- Date Needed -->
                <div class="flex-1">
                    <x-forms.textarea id="date_needed" label="Date Needed" model="form.date_needed"
                        :form="$form" :required="true" :maxlength="500" rows="4" />
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
                    <x-forms.early-procurement model="form.early_procurement" :form="$form" :clickable="false" />
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
                        :required="true" :searchable="false" />
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
        class="fixed bottom-5 right-0 left-0 lg:ml-[13.75rem] flex justify-end p-2 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 z-49">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-end">
            <button wire:click="save"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save
            </button>
        </div>
    </div>

</div>
