<x-forms.modal title="Procurement Details" size="max-w-6xl" wire:model="showModal">

    <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
        <!-- Grid for PR No. + Program/Project -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- PR Number -->
            <div class="col-span-1">
                <x-forms.readonly-input id="pr_number" label="PR No." model="form.pr_number" :form="$form"
                    :required="true" :colspan="1" textAlign="right" :viewOnly="true" class="flex-1" />
            </div>


            <!-- Procurement Program / Project -->
            <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                model="form.procurement_program_project" :form="$form" :required="true" :maxlength="500"
                :rows="1" colspan="col-span-4 " :viewOnly="true" />
        </div>

        <!-- Per Lot / Per Item Toggle + Table -->
        <div class="mt-6 flex flex-col md:flex-row md:items-start md:space-x-6">
            <!-- Toggle -->
            <div class="flex items-center gap-x-3">
                <label class="text-sm text-gray-500">Per Lot</label>

                <label class="relative inline-block w-11 h-6 cursor-pointer">
                    <input type="checkbox" class="peer sr-only"
                        @change="$event.target.checked
        ? @this.set('form.procurement_type', 'perItem')
        : @this.set('form.procurement_type', 'perLot')"
                        {{ ($form['procurement_type'] ?? '') === 'perItem' ? 'checked' : '' }}>
                    <span
                        class="absolute inset-0 bg-blue-600 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>
                    <span
                        class="absolute top-1/2 start-0.5 -translate-y-1/2 size-5 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full"></span>
                </label>

                <label class="text-sm text-gray-500">Per Item</label>
            </div>

            <!-- Table shows only when "Per Item" is selected -->
            @if (($form['procurement_type'] ?? '') === 'perItem')
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
                            <h3 class="font-semibold text-gray-700">Item List</h3>
                        </div>
                    </div>

                    {{-- Table --}}
                    @if ($showTable)
                        <div class="overflow-x-auto">
                            {{-- Add Item button --}}
                            <div class="flex justify-end mb-2">
                                <button type="button" wire:click="addItem"
                                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
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
                                            Item No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Description
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($form['items'] as $index => $item)
                                        <tr>
                                            <td class="px-6 py-4 text-center text-sm text-gray-800">
                                                <input type="text"
                                                    class="border border-gray-300 rounded-lg px-2 py-1 w-20 focus:ring-emerald-500 focus:border-emerald-500 text-center"
                                                    placeholder="#"
                                                    wire:model.defer="form.items.{{ $index }}.item_no">
                                            </td>
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
        <div class="grid grid-cols-2 md:grid-cols-8 gap-4">
            <!-- Date Receipt (Advance Copy) -->
            <x-forms.date id="date_receipt" label="Date Receipt" model="form.date_receipt" :form="$form"
                :required="false" :viewOnly="true" colspan="col-span-1" />
            <!-- Category -->
            <x-forms.select id="category_id" label="Category" model="form.category_id" :form="$form"
                :options="$categories" optionValue="id" optionLabel="category" :required="true" wireModifier="lazy"
                :viewOnly="true" colspan="col-span-2" />
            <!-- Category Type (Read-only) -->
            <x-forms.readonly-input id="category_type" label="Category Type" model="form.category_type"
                :form="$form" :required="false" :viewOnly="true" :colspan="1" />
            <!-- RBAC / SBAC (Read-only) -->
            <x-forms.readonly-input id="rbac_sbac" label="RBAC / SBAC" model="form.rbac_sbac" :form="$form"
                :required="false" :viewOnly="true" :colspan="1" />
            <!-- DTRACK Number -->
            <x-forms.input id="dtrack_no" label="DTRACK #" model="form.dtrack_no" :form="$form" :required="true"
                :viewOnly="true" colspan="col-span-1" />
            <!-- UniCode -->
            <x-forms.input id="unicode" label="UniCode" model="form.unicode" :form="$form" :viewOnly="true"
                :required="false" />
            <!-- Division -->
            <x-forms.select id="divisions_id" label="Division" model="form.divisions_id" :form="$form"
                :options="$divisions" optionValue="id" optionLabel="divisions" :required="true" :viewOnly="true"
                colspan="col-span-3" />
            <!-- Cluster / Committee -->
            <x-forms.select id="cluster_committees_id" label="Cluster / Committee" model="form.cluster_committees_id"
                :form="$form" :options="$clusterCommittees" optionValue="id" optionLabel="clustercommittee"
                :required="true" :viewOnly="true" colspan="col-span-2" />

        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mt-6">
        <!-- Simple Form Fields in Landscape Layout -->
        <div class="grid grid-cols-4 gap-4">
            <!-- Venue Specific -->
            <x-forms.select id="venue_specific_id" label="Venue|Specific" model="form.venue_specific_id"
                :form="$form" :options="$venueSpecifics" optionValue="id" optionLabel="name" :required="false"
                :viewOnly="true" colspan="col-span-1" />
            <!-- Venue Province/HUC -->
            <x-forms.select id="venue_province_huc_id" label="Venue|Province/HUC" model="form.venue_province_huc_id"
                :form="$form" :options="$venueProvinces" optionValue="id" optionLabel="province_huc" :viewOnly="true"
                :required="false" colspan="col-span-1" />
            <!-- Category / Venue (Read-only) -->
            <x-forms.readonly-input id="category_venue" label="Category / Venue" model="form.category_venue"
                :form="$form" :required="false" :viewOnly="true" colspan="col-span-2" />
            <!-- Approved PPMP -->
            <div class="flex flex-col col-span-2">
                <x-forms.approved-ppmp :form="$form" model="form.approved_ppmp" :viewOnly="true"
                    othersModel="otherPPMP" />
            </div>
            <div class="flex flex-col col-span-2">
                <!-- APP Updated -->
                <x-forms.app-updated :form="$form" model="form.app_updated" :viewOnly="true"
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
                        model="form.immediate_date_needed" :form="$form" :viewOnly="true" :maxlength="500"
                        rows="4" />

                </div>

                <!-- Date Needed -->
                <div class="flex-1">
                    <x-forms.textarea id="date_needed" label="Date Needed" model="form.date_needed"
                        :form="$form" :required="false" :maxlength="500" :viewOnly="true" rows="4" />
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-span-1 flex flex-col gap-4">
                <!-- PMO/End-User -->
                <div>
                    <x-forms.select id="end_users_id" label="PMO/End-User" model="form.end_users_id"
                        :form="$form" :options="$endUsers" optionValue="id" optionLabel="endusers"
                        :viewOnly="true" :required="false" />

                </div>
                <!-- Early Procurement Toggle -->
                <div>
                    <x-forms.early-procurement model="form.early_procurement" :form="$form" :viewOnly="true"
                        :clickable="false" />
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
                    <x-forms.select id="fund_source_id" label="Source of Funds" model="form.fund_source_id"
                        :form="$form" :options="$fundSources" optionValue="id" optionLabel="fundsources"
                        :viewOnly="true" :required="true" />
                </div>

                <!-- Expense Class -->
                <div class="col-span-1">
                    <x-forms.input id="expense_class" label="Expense Class" model="form.expense_class"
                        :form="$form" :required="false" :viewOnly="true" textAlign="right" />
                </div>

                <!-- ABC Amount -->
                <x-forms.currency-input id="abc" label="ABC Amount" model="form.abc" :form="$form"
                    :required="true" colspan="col-span-1" :viewOnly="true" wireModifier="live" />


                <!-- ABC ⇔ 50k -->
                <div class="col-span-1">
                    <x-forms.abc50k id="abc_50k" label="ABC ⇔ 50k" model="form.abc_50k" :viewOnly="true"
                        :form="$form" />

                </div>

            </div>
        </div>
    </div>
</x-forms.modal>
