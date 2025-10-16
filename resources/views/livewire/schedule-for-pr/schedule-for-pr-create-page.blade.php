<div class="space-y-6 px-2 pb-[5rem]">
    <div class="relative bg-white rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <div
            class="absolute top-0 left-0 bg-emerald-600 text-white text-xs font-semibold px-2 py-0.5 rounded-tl-xl rounded-br-xl">
            {{ $procurementType === 'perLot' ? 'Per Lot' : 'Per Item' }}
        </div>



        <div class="space-y-8 p-4 pt-5">

            <div class="grid grid-cols-4 md:grid-cols-7 gap-4">
                <x-forms.input id="ib_number" label="IB Number" model="form.ib_number" :form="$form" :required="true"
                    colspan="col-span-1" />

                <x-forms.date id="opening_of_bids" label="Opening of Bids" model="form.opening_of_bids" :form="$form"
                    :required="true" colspan="col-span-1" />

                <x-forms.textarea id="project_name" label="Project Name" model="form.project_name" :form="$form"
                    :required="true" colspan="col-span-full" :rows="1" />

                <x-forms.yes-no-toggle id="is_framework" label="Framework" model="form.is_framework" :form="$form"
                    colspan="col-span-1" />

                <div class="col-span-6"></div>

                <x-forms.select id="status_id" label="Bidding Status" model="form.status_id" :form="$form"
                    :options="$biddingStatus" optionValue="id" optionLabel="name" :required="false" colspan="col-span-1" />

                <x-forms.select id="action_taken" label="Action Taken" model="form.action_taken" :form="$form"
                    :options="$ActionTakenOptions" optionValue="id" optionLabel="name" :required="false" colspan="col-span-1" />

                <x-forms.date id="next_bidding_schedule" label="Next Bid Schedule" model="form.next_bidding_schedule"
                    :form="$form" colspan="col-span-1" />

                <x-forms.input id="document_url" type="text" label="Google Drive Link" model="form.filepath"
                    placeholder="http://example.com/path/to/document.pdf" :required="true" colspan="col-span-4" />

            </div>

        </div>

    </div>

    <div>
        <div class="flex items-end gap-x-4">

            {{-- Your existing "Select" button --}}
            <button wire:click="openSelectionModal"
                class="p-2 px-2 inline-flex items-center text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 h-10">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Select
            </button>

            {{-- Spacer to push the totals to the right --}}
            <div class="flex-grow"></div>

            {{-- Add the new readonly input fields --}}
            <x-forms.readonly-input id="total_abc" label="Total ABC" model="totalAbcFormatted" :form="$this"
                :textAlign="'right'" colspan="w-40" {{-- Use width instead of colspan in flex --}} />

            <x-forms.readonly-input id="two_percent" label="2%" model="twoPercent" :form="$this"
                :textAlign="'right'" colspan="w-40" />

            <x-forms.readonly-input id="five_percent" label="5%" model="fivePercent" :form="$this"
                :textAlign="'right'" colspan="w-40" />
        </div>

        {{-- This section displays the selected procurements/items --}}
        @if (!empty($selectedProcurements))
            <div class="mt-2 space-y-6">

                {{-- SELECTED LOTS TABLE --}}
                @if (!empty($selectedLots))
                    <div
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">
                        <h3
                            class="text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-neutral-800 p-3">
                            Selected PR
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs bg-white dark:bg-neutral-900">
                                <thead class="bg-gray-200 dark:bg-neutral-900">
                                    <tr>
                                        <th class="p-2 text-left font-semibold text-black dark:text-white w-20">
                                            PR No.</th>
                                        <th class="p-2 text-left font-semibold text-black dark:text-white">
                                            Procurement Program / Project</th>
                                        <th class="p-2 text-center font-semibold text-black dark:text-white w-32">
                                            Amount</th>
                                        <th class="p-2 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                    @foreach ($selectedLots as $procIndex => $proc)
                                        <tr wire:key="lot-{{ $proc['id'] }}"
                                            class="hover:bg-gray-50 dark:hover:bg-neutral-800/50">
                                            <td class="p-2 whitespace-nowrap text-black dark:text-white">
                                                {{ $proc['pr_number'] }}</td>
                                            <td class="p-2 text-black dark:text-white">
                                                {{ $proc['procurement_program_project'] }}
                                            </td>
                                            <td class="p-2 text-right whitespace-nowrap text-black dark:text-white">
                                                ₱{{ number_format($proc['abc'] ?? 0, 2) }}</td>
                                            <td class="p-2 text-center ">
                                                <button wire:click.prevent="removeLot({{ $procIndex }})"
                                                    class="font-medium text-red-500 hover:text-red-700 text-lg">×</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- SELECTED ITEMS TABLE --}}
                @if (!empty($selectedItemGroups))
                    <div
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">
                        <h3
                            class="text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-neutral-800 p-3 ">
                            Selected Items
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs bg-white dark:bg-neutral-900">
                                <thead class="bg-gray-200 dark:bg-neutral-900">
                                    <tr>
                                        <th class="p-2 text-left font-semibold text-black dark:text-white w-20">
                                            PR No.</th>
                                        <th class="p-2 text-left font-semibold text-black dark:text-white">
                                            Item Description</th>
                                        <th class="p-2 text-center font-semibold text-black dark:text-white w-32">
                                            Amount</th>
                                        <th class="p-2 w-6"></th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                    @foreach ($selectedItemGroups as $procIndex => $proc)
                                        @foreach ($proc['items'] as $itemIndex => $item)
                                            <tr wire:key="item-{{ $item['id'] }}"
                                                class="hover:bg-gray-50 dark:hover:bg-neutral-800/50">
                                                <td
                                                    class="p-2 whitespace-nowrap text-black dark:text-white dark:text-neutral-400">
                                                    {{ $proc['pr_number'] }}</td>
                                                <td class="p-2 text-black dark:text-white">
                                                    {{ $item['description'] }}</td>
                                                <td
                                                    class="p-2 text-right whitespace-nowrap text-black dark:text-white">
                                                    ₱{{ number_format($item['amount'] ?? 0, 2) }}</td>
                                                <td class="p-2 text-center">
                                                    <button
                                                        wire:click.prevent="removeItem({{ $procIndex }}, {{ $itemIndex }})"
                                                        class="font-medium text-red-500 hover:text-red-700 text-lg">×</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        @endif

    </div>

    <livewire:schedule-for-pr.select-modal :procurementType="$procurementType" :existing-lot-ids="$existingLotIds" :existing-item-ids="$existingItemIds" />

    {{-- Fixed Action Bar with Save Button --}}
    <div
        class="fixed bottom-5 right-0 left-0 lg:ml-[13.75rem] flex justify-end p-2 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 z-49">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-end">
            <button wire:click="save" wire:loading.attr="disabled"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                <div wire:loading wire:target="save"
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white">
                </div>
                Save
            </button>
        </div>
    </div>

</div>
</div>
