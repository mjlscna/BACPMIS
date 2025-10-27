<div class="space-y-6 px-2 pb-[5rem]">
    <div class="relative bg-white rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <div
            class="absolute top-0 left-0 bg-emerald-600 text-white text-xs font-semibold px-2 py-0.5 rounded-tl-xl rounded-br-xl">
            {{ $procurementType === 'perLot' ? 'Per Lot' : 'Per Item' }}
        </div>

        <div class="space-y-8 p-4 pt-5">

            <div class="grid grid-cols-7 md:grid-cols-10 gap-4">

                <x-forms.input id="ib_number" label="IB Number" model="form.ib_number" :form="$form" :required="true"
                    colspan="col-span-1" />

                <x-forms.date id="opening_of_bids" label="Opening of Bids" model="form.opening_of_bids" :form="$form"
                    :required="true" colspan="col-span-1" />

                <x-forms.textarea id="project_name" label="Project Name" model="form.project_name" :form="$form"
                    :required="true" colspan="col-span-full" :rows="1" />

                <x-forms.yes-no-toggle id="is_framework" label="Framework" model="form.is_framework" :form="$form"
                    colspan="col-span-1" />

                {{-- This is the original "col-span-5" spacer from your edit page --}}
                <div class="col-span-5"></div>

                {{-- This is the original "Current:" link block from your edit page --}}
                <div class="col-span-1">
                    <div class="flex items-end-safe gap-x-2">
                        <span class="font-medium text-gray-700 dark:text-gray-200">Current:</span>

                        <a href="{{ $form['filepath'] }}" target="_blank" rel="noopener noreferrer"
                            class="text-emerald-600 hover:text-emerald-700 focus:outline-none" title="View Document">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path fill-rule="evenodd"
                                    d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75h-3.44a.75.75 0 0 1 0-1.5Zm-10.5 4.5a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5h10.5a1.5 1.5 0 0 0 1.5-1.5V10.5a.75.75 0 0 1 1.5 0v8.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V8.25a3 3 0 0 1 3-3h8.25a.75.75 0 0 1 0 1.5H5.25Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>

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

            {{-- These are the original readonly inputs from your edit page, as requested --}}
            <x-forms.readonly-input id="total_abc" label="Total ABC" model="totalAbcFormatted" :form="$this"
                :textAlign="'right'" colspan="w-32" {{-- Use width instead of colspan in flex --}} />

            <x-forms.readonly-input id="two_percent" label="2%" model="twoPercent" :form="$this"
                :textAlign="'right'" colspan="w-32" />

            <x-forms.readonly-input id="five_percent" label="5%" model="fivePercent" :form="$this"
                :textAlign="'right'" colspan="w-32" />

            <x-forms.readonly-input id="thirty_percent" label="30%" model="thirtyPercent" :form="$this"
                :textAlign="'right'" colspan="w-32" />
        </div>

        {{-- This is the new, updated table section from your create page --}}
        @if (!empty($selectedProcurements))
            <div class="mt-2 space-y-6">
                <div
                    class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

                    {{-- Dynamic Header Title --}}
                    <h3
                        class="text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-neutral-800 p-2 border-b border-gray-200 dark:border-neutral-700">
                        @if ($procurementType === 'perLot')
                            Selected PR
                        @else
                            Selected Items
                        @endif
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">

                            <thead class="sticky bg-gray-200 dark:bg-neutral-900">
                                <tr>
                                    <th
                                        class="px-2 py-1 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 w-20">
                                        PR No.</th>
                                    <th
                                        class="px-2 py-1 text-left font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600">
                                        @if ($procurementType === 'perLot')
                                            Procurement Program / Project
                                        @else
                                            Item Description
                                        @endif
                                    </th>
                                    <th
                                        class="px-2 py-1 text-right font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 w-32">
                                        Amount</th>
                                    <th
                                        class="px-2 py-1 text-center font-semibold text-black dark:text-white w-12 border-b border-gray-300 dark:border-neutral-600">
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @foreach ($this->SelectedPR as $pr)
                                    <tr wire:key="selected-pr-{{ $pr['id'] }}">
                                        <td class="px-2 py-1 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $pr['pr_number'] }}
                                        </td>
                                        <td class="px-2 py-1 text-gray-900 dark:text-gray-100">
                                            {{ $pr['description'] ?? $pr['procurement_program_project'] }}
                                        </td>
                                        <td
                                            class="px-2 py-1 text-right text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            <span class="text-gray-500">₱</span>
                                            <span>{{ number_format($pr['amount'] ?? ($pr['abc'] ?? 0), 2) }}</span>
                                        </td>
                                        <td class="px-2 py-1 text-center">
                                            <button wire:click.prevent="removeSelectedPR({{ $pr['id'] }})"
                                                class="font-medium text-red-500 hover:text-red-700 text-base">×</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($this->SelectedPR->isNotEmpty() && $this->SelectedPR->hasPages())
                        <div
                            class="flex-shrink-0 border-t border-gray-200 dark:border-neutral-700 px-4 py-2 bg-white dark:bg-neutral-900 grid grid-cols-3 items-center">

                            {{-- Column 1: Item Count (Left Aligned) --}}
                            <div class="text-xs text-gray-500 text-left">
                                Showing {{ $this->SelectedPR->firstItem() }} to {{ $this->SelectedPR->lastItem() }} of
                                {{ $this->SelectedPR->total() }} items
                            </div>

                            {{-- Column 2: Pagination (Center Aligned) --}}
                            <nav role="navigation" aria-label="Pagination Navigation"
                                class="flex justify-center items-center gap-3">

                                {{-- Previous Button --}}
                                <button wire:click.prevent="previousCustomPage('selectedPRPage')"
                                    @disabled($this->SelectedPR->onFirstPage())
                                    class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-5">
                                        <path fill-rule="evenodd"
                                            d="M10.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L12.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"
                                            clip-rule="evenodd" />
                                        <path fill-rule="evenodd"
                                            d="M4.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L6.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>

                                {{-- Page Info --}}
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $this->SelectedPR->currentPage() }} of
                                    {{ $this->SelectedPR->lastPage() }}
                                </span>

                                {{-- Next Button --}}
                                <button wire:click.prevent="nextCustomPage('selectedPRPage')"
                                    @disabled(!$this->SelectedPR->hasMorePages())
                                    class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-5">
                                        <path fill-rule="evenodd"
                                            d="M13.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L11.69 12 4.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z"
                                            clip-rule="evenodd" />
                                        <path fill-rule="evenodd"
                                            d="M19.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06L17.69 12l-6.97-6.97a.75.75 0 0 1 1.06-1.06l7.5 7.5Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>

                            {{-- Column 3: Empty Spacer (Right Aligned) --}}
                            <div></div>

                        </div>
                    @endif
                </div>
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
