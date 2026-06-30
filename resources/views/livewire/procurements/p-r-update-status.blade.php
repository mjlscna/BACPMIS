<div class="space-y-6">

    {{-- First Box --}}
    <div
        class="bg-white p-4 rounded-xl shadow border border-gray-200
            dark:bg-neutral-700 dark:border-neutral-700">
        <!-- Grid for PR No. + Program/Project -->
        <div class="grid grid-cols-2 md:grid-cols-10 gap-4">
            <!-- PR Number -->
            <div class="col-span-1">
                <x-forms.input id="pr_number" label="PR No." model="form.pr_number" :form="$form" :required="true"
                    textAlign="right" :readonly="true" :disabled="true" />
            </div>

            <!-- Procurement Program / Project -->
            <x-forms.textarea id="procurement_program_project" label="Procurement Program / Project"
                model="form.procurement_program_project" :form="$form" :required="true" :maxlength="1000"
                :rows="1" colspan="col-span-9" :readonly="true" :disabled="true" :autoResize="true" />
        </div>

        <!-- Per Lot / Per Item Toggle + Table -->
        <div class="mt-6 flex flex-col md:flex-row md:items-start md:space-x-6">
            <!-- Toggle -->
            <div class="flex items-center gap-x-3">
                <x-forms.prType id="procurement-toggle" model="form.procurement_type" :form="$form"
                    :clickable="false" />
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <!-- Table shows only when "Per Item" is selected -->
        @if ($form['procurement_type'] === 'perItem')
            <div class="mt-4 md:mt-0 w-full">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700 dark:text-white">Update Item Status</h3>
                </div>

                {{-- Items table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 rounded-xl">
                        <thead class="bg-gray-200 dark:bg-neutral-900">
                            <tr>
                                <th
                                    class="px-3 py-2 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-8">
                                    No.
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Description
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-16">
                                    Amount
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-52">
                                    Stage
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-32">
                                    Remarks
                                </th>
                                <th
                                    class="px-3 py-2 text-center text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-64">
                                    Notes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-700 divide-y divide-gray-200 dark:divide-gray-200">
                            @forelse ($form['items'] ?? [] as $rowIndex => $item)
                                @php
                                    $currentStageId = $item['prstage']['pr_stage_id'] ?? null;
                                @endphp
                                <tr wire:key="item-{{ $item['uid'] ?? ($item['prItemID'] ?? $rowIndex) }}">
                                    <td class="px-3 py-2">
                                        <div class="text-gray-700 dark:text-white text-center text-sm">
                                            {{ $item['item_no'] ?? '' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-gray-700 dark:text-white text-sm">
                                            {{ $item['description'] ?? '' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-center text-gray-700 dark:text-white text-sm">
                                            {{ is_numeric($item['amount'] ?? null) ? number_format($item['amount'], 2, '.', ',') : '0.00' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model="itemStages.{{ $item['prItemID'] }}"
                                            class="block w-full px-2 py-1  text-sm border border-gray-300 dark:border-neutral-600 rounded-lg
                                           focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                                           dark:bg-neutral-700 dark:text-white">
                                            <option value="">Select Stage</option>
                                            @foreach ($stages as $stage)
                                                <option value="{{ $stage->id }}"
                                                    @if ($currentStageId == $stage->id) selected @endif>
                                                    {{ $stage->procurementstage }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model="itemRemarks.{{ $item['prItemID'] }}"
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg
                                           focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                                           dark:bg-neutral-700 dark:text-white">
                                            <option value="">Select</option>
                                            @foreach ($remarks as $remark)
                                                <option value="{{ $remark->id }}">{{ $remark->remarks }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <textarea wire:model.defer="itemNotes.{{ $item['prItemID'] }}" rows="1" maxlength="5000"
                                            placeholder="Add notes..."
                                            class="block w-full px-2 py-1 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg
                                       focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                                       dark:bg-neutral-700 dark:text-white resize-none overflow-hidden"
                                            style="resize: none; overflow: hidden;" x-data="{
                                                autoResize() {
                                                    $el.style.height = 'auto';
                                                    $el.style.height = $el.scrollHeight + 'px';
                                                }
                                            }" x-init="autoResize()" @input="autoResize()"></textarea>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No items found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Stage Selection for Per Lot -->
            <div class="mt-1 grid grid-cols-7 gap-4">
                <x-forms.select id="selectedStageId" label="Procurement Stage" model="selectedStageId"
                    :form="[]" :options="$stages" optionValue="id" optionLabel="procurementstage"
                    :required="false" colspan="col-span-1" :searchable="true" />

                <x-forms.select id="remarksId" label="Remark" model="remarksId" :form="[]" :options="$remarks"
                    optionValue="id" optionLabel="remarks" :required="false" colspan="col-span-1" :searchable="true" />

                <x-forms.textarea id="lotNotes" label="Notes" model="lotNotes" :form="[]" :required="false"
                    :maxlength="5000" :rows="1" colspan="col-span-5" placeholder="Notes here"
                    :autoResize="true" />
            </div>
        @endif
    </div>


    <div
        class="fixed bottom-4 right-0 left-0 lg:left-48 flex justify-end px-4 py-3 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-700 shadow-lg z-30">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-end gap-3">
            <button wire:click="cancel"
                class="flex items-center gap-2 px-2 py-2 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                Cancel
            </button>
            <button wire:click="save"
                class="flex items-center gap-2 px-2 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save
            </button>
        </div>
    </div>
    <!-- Bottom Spacer to prevent content hiding under fixed footer and overall footer -->
    <div class="h-32"></div>
</div>
