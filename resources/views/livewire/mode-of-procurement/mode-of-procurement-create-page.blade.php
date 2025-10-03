<div class="space-y-6 pb-[5rem]">

    <div class="p-4 bg-white rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        @php
            $canAccessTab2 = !empty($procID);
            $canAccessTab3 = !empty($procID);
        @endphp

        <ul class="flex items-center w-full max-w-7xl py-2 bg-white dark:bg-neutral-800 dark:border-neutral-700 mx-48"
            data-hs-stepper='{"isCompleted": true}'>

            <!-- Step 1 -->
            <li class="flex items-center gap-x-2 basis-1/3 group"
                data-hs-stepper-nav-item='{"index": 1, "isCompleted": {{ $activeTab > 1 ? ' true' : 'false' }}
                        }'>
                <button type="button" wire:click="switchTab(1)"
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 1 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 1 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                    1
                </button>
                <span class="text-sm font-medium text-gray-800 dark:text-white whitespace-nowrap">
                    IB Details
                </span>
                <!-- Dynamic Line -->
                <div
                    class="h-px w-48 ml-3 mr-3 transition-colors duration-300
            {{ $activeTab > 1 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-700' }}">
                </div>
            </li>

            <!-- Step 2 -->
            <li class="flex items-center gap-x-2 basis-1/3 group"
                data-hs-stepper-nav-item='{"index": 2, "isCompleted": {{ $activeTab > 2 ? ' true' : 'false' }}
                        }'>
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
                data-hs-stepper-nav-item='{"index": 3, "isCompleted": {{ $activeTab > 3 ? ' true' : 'false' }}
                        }'>
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



        <hr class="my-6 border-gray-200 dark:border-neutral-600">

        <div>
            @if ($activeTab == 1)
                <div class="space-y-6">
                    <div
                        class="relative bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
                        <div
                            class="absolute top-0 left-0 bg-emerald-600 text-white text-xs font-semibold px-2 py-0.5 rounded-tl-xl rounded-br-xl">
                            {{ $procurementType === 'perLot' ? 'Per Lot' : 'Per Item' }}
                        </div>
                        <div class="grid grid-cols-9 gap-4 mt-4">
                            <x-forms.input textAlign="right" id="ib_no" label="IB No." model="form.ib_no"
                                :form="$form" colspan="col-span-1" />
                            <x-forms.textarea id="" label="Title" model="form.procurement_program_project"
                                :form="$form" :required="true" :maxlength="1000" :rows="1"
                                colspan="col-span-8" />
                        </div>
                    </div>

                    <button x-on:click="$dispatch('open-mode-modal')"
                        class="p-2 px-2 inline-flex items-center text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Select Procurements
                    </button>

                    @if (!empty($selectedProcurements))
                        <div
                            class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Selected Procurements
                                ({{ count($selectedProcurements) }})</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-100 dark:bg-neutral-800">
                                        <tr>
                                            <th class="p-1 text-left font-semibold text-black dark:text-white w-12">PR
                                                No.
                                            </th>
                                            <th
                                                class="p-1 text-left font-semibold text-black dark:text-white whitespace-nowrap w-96">
                                                Program/Project</th>
                                            <th
                                                class="p-1 text-center font-semibold text-black dark:text-white whitespace-nowrap w-16">
                                                Division</th>
                                            <th class="p-1 text-center font-semibold text-black dark:text-white w-24">
                                                ABC
                                                Amount
                                            </th>
                                            <th class="p-1 text-center font-semibold text-black dark:text-white w-2">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-600">
                                        @foreach ($selectedProcurements as $index => $proc)
                                            <tr wire:key="selected-proc-{{ $proc['id'] }}">
                                                <td class="p-2 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                                    {{ $proc['pr_number'] }}</td>
                                                <td class="p-2 text-gray-900 dark:text-gray-100 w-96">
                                                    {{ $proc['procurement_program_project'] }}</td>
                                                <td
                                                    class="px-1 py-1 text-center  text-xs text-black dark:text-white w-16">
                                                    {{ $proc['division_abbreviation'] }}</td>
                                                <td
                                                    class="p-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-100 w-24">
                                                    <span class="text-gray-500">₱</span>
                                                    <span>{{ number_format($proc['abc'] ?? 0, 2) }}</span>
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button"
                                                        wire:click.prevent="removeSelectedItem({{ $index }})"
                                                        class="font-medium text-red-500 hover:text-red-700">
                                                        X
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            @if ($activeTab == 2)
                <div
                    class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bidding Schedules</h3>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">Your form components for bidding schedules will go
                        here.
                    </p>
                </div>
            @endif

            @if ($activeTab == 3)
                <div
                    class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Awarding</h3>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">Your form components for awarding will go here.</p>
                </div>
            @endif
        </div>
    </div>


    <div
        class="fixed bottom-0 right-0 left-0 lg:ml-[13.75rem] flex justify-end p-4 border-t border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 z-50">
        <div class="w-full max-w-[110rem] mx-auto sm:px-6 lg:px-8 flex justify-between items-center">
            <div>
                @if ($activeTab > 1)
                    <button wire:click="previousStep"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-neutral-800 dark:text-gray-200 dark:border-neutral-700 dark:hover:bg-neutral-700">
                        Previous
                    </button>
                @endif
            </div>

            <div>
                @if ($activeTab < 3)
                    <button wire:click="nextStep"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                        Next
                    </button>
                @elseif ($activeTab == 3)
                    <button wire:click="save"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save
                    </button>
                @endif
            </div>
        </div>
    </div>

    <livewire:mode-of-procurement.mode-proc-select-modal :procurementType="$procurementType" :existingSelection="$form['procurement_ids'] ?? []" />
</div>
