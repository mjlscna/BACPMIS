<div class="space-y-6 px-2 pb-[5rem]">
    <div class="relative bg-white rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <div
            class="absolute top-0 left-0 bg-emerald-600 text-white text-xs font-semibold px-2 py-0.5 rounded-tl-xl rounded-br-xl">
            {{ $procurementType === 'perLot' ? 'Per Lot' : 'Per Item' }}
        </div>


        <ul class="flex items-center w-full max-w-6xl pt-2 p-2 bg-white dark:bg-neutral-700 dark:border-neutral-700 mx-auto"
            data-hs-stepper='{"isCompleted": true}'>

            <li class="flex items-center gap-x-2 flex-1 group"
                data-hs-stepper-nav-item='{"index": 1, "isCompleted": {{ $activeTab > 1 ? 'true' : 'false' }} }'>
                <button type="button" wire:click="switchTab(1)"
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
          {{ $activeTab == 1 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 1 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                    1
                </button>
                <span class="text-sm font-medium text-black dark:text-white whitespace-nowrap">
                    MOP Details
                </span>
                <div
                    class="h-px grow transition-colors duration-300
          {{ $activeTab > 1 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-500' }}">
                </div>
            </li>

            <li class="flex items-center gap-x-2 flex-1 group"
                data-hs-stepper-nav-item='{"index": 2, "isCompleted": {{ $activeTab > 2 ? 'true' : 'false' }} }'>
                <button type="button" wire:click="switchTab(2)" @if (!$mopGroupId) disabled @endif
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
          {{ $activeTab == 2 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 2 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-neutral-400 cursor-not-allowed') }}">
                    2
                </button>
                <span
                    class="text-sm font-medium whitespace-nowrap {{ $activeTab > 2 ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                    Mode of Procurement
                </span>
                <div
                    class="h-px grow transition-colors duration-300
          {{ $activeTab > 2 ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-500' }}">
                </div>
            </li>

            <li class="flex items-center gap-x-2 group"
                data-hs-stepper-nav-item='{"index": 3, "isCompleted": {{ $activeTab > 3 ? 'true' : 'false' }} }'>
                <button type="button" @if ($activeTab) wire:click="switchTab(3)" @endif
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
          {{ $activeTab == 3 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 3 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-neutral-400 cursor-not-allowed') }}">
                    3
                </button>
                <span
                    class="text-sm font-medium whitespace-nowrap {{ $activeTab > 3 ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                    Post
                </span>
            </li>
        </ul>

        <hr class=" border-gray-200 dark:border-neutral-600">

        <div>
            @if ($activeTab == 1)
                <div class="p-4">
                    <button x-on:click="$dispatch('open-mode-modal')"
                        class="p-2 px-2 inline-flex items-center text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Select
                    </button>

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
                                                    <th
                                                        class="p-2 text-left font-semibold text-black dark:text-white w-20">
                                                        PR No.</th>
                                                    <th class="p-2 text-left font-semibold text-black dark:text-white">
                                                        Procurement Program / Project</th>
                                                    <th
                                                        class="p-2 text-center font-semibold text-black dark:text-white w-32">
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
                                                        <td
                                                            class="p-2 text-right whitespace-nowrap text-black dark:text-white">
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
                                                    <th
                                                        class="p-2 text-left font-semibold text-black dark:text-white w-20">
                                                        PR No.</th>
                                                    <th class="p-2 text-left font-semibold text-black dark:text-white">
                                                        Item Description</th>
                                                    <th
                                                        class="p-2 text-center font-semibold text-black dark:text-white w-32">
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

        <livewire:mode-of-procurement.mode-proc-select-modal :procurementType="$procurementType" :existing-lot-ids="$existingLotIds" :existing-item-ids="$existingItemIds" />

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
