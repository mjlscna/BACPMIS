@if (session()->has('success_message'))
    <script>
        document.addEventListener('alpine:init', () => {
            window.Livewire.dispatch('swal:alert', {
                type: 'success',
                title: 'Selections Saved!',
                text: '{{ session('success_message') }}',
                toast: true,
                position: 'top-end'
            });
        });
    </script>
@endif

<div class="space-y-6 px-2 pb-[5rem]">
    <div class="relative bg-white rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
        <div
            class="absolute top-0 left-0 bg-emerald-600 text-white text-xs font-semibold px-2 py-0.5 rounded-tl-xl rounded-br-xl">
            {{ $procurementType === 'perLot' ? 'Per Lot' : 'Per Item' }}
        </div>


        <ul class="flex items-center w-full max-w-6xl pt-2 p-2 bg-white dark:bg-neutral-700 dark:border-neutral-700 mx-auto"
            data-hs-stepper='{"isCompleted": true}'>

            <li class="flex items-center gap-x-2 flex-1 group"
                data-hs-stepper-nav-item='{"index": 1, "isCompleted": {{ $activeTab > 1 || $mopGroupId ? 'true' : 'false' }} }'>
                <button type="button" wire:click="setStep(1)"
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{-- Updated logic here: also check for $mopGroupId --}}
            {{ $activeTab == 1 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 1 || $mopGroupId ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800') }}">
                    1
                </button>
                <span class="text-sm font-medium text-black dark:text-white whitespace-nowrap">
                    MOP Details
                </span>
                <div
                    class="h-px grow transition-colors duration-300
            {{-- This is the line logic you wanted fixed --}}
            {{ $activeTab > 1 || $mopGroupId ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-500' }}">
                </div>
            </li>

            <li class="flex items-center gap-x-2 flex-1 group" {{-- This 'isCompleted' logic is now updated --}}
                data-hs-stepper-nav-item='{"index": 2, "isCompleted": {{ $activeTab > 2 || $mopGroupId ? 'true' : 'false' }} }'>
                <button type="button" wire:click="setStep(2)" @if (!$mopGroupId) disabled @endif
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 2 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 2 || $mopGroupId ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-neutral-400 cursor-not-allowed') }}">
                    2
                </button>
                <span
                    class="text-sm font-medium whitespace-nowrap
                    {{ $activeTab > 2 || $mopGroupId ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                    Mode of Procurement
                </span>
                <div
                    class="h-px grow transition-colors duration-300
            {{ $activeTab > 2 || $mopGroupId ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-neutral-500' }}">
                </div>
            </li>

            <li class="flex items-center gap-x-2 group" {{-- Updated 'isCompleted' logic --}}
                data-hs-stepper-nav-item='{"index": 3, "isCompleted": {{ $activeTab > 3 ? 'true' : 'false' }} }'>
                {{-- Added disabled check and updated classes --}}
                <button type="button" wire:click="setStep(3)" @if (!$mopGroupId) disabled @endif
                    class="size-8 flex justify-center items-center rounded-full font-medium text-sm transition
            {{ $activeTab == 3 ? 'bg-green-500 text-white border-2 border-emerald-700' : ($activeTab > 3 ? 'bg-emerald-600 text-white' : ($mopGroupId ? 'bg-gray-100 text-gray-800' : 'bg-gray-100 text-neutral-400 cursor-not-allowed')) }}">
                    3
                </button>
                <span
                    class="text-sm font-medium whitespace-nowrap
                    {{-- Updated text color logic --}}
                    {{ $activeTab > 3 || $mopGroupId ? 'text-gray-800 dark:text-white' : 'text-neutral-400 dark:text-neutral-500' }}">
                    Post
                </span>
            </li>
        </ul>

        <hr class=" border-gray-200 dark:border-neutral-600">

        <div>
            @if ($activeTab == 1)
                <div class="p-4">
                    <button wire:click="openSelectionModal"
                        class="p-2 px-2 inline-flex items-center text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 h-10">
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
                                                    class="px-2 py-1 text-center font-semibold text-black dark:text-white border-b border-gray-300 dark:border-neutral-600 w-32">
                                                    Amount</th>
                                                <th
                                                    class="px-2 py-1 text-center font-semibold text-black dark:text-white w-12 border-b border-gray-300 dark:border-neutral-600">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                            @foreach ($this->SelectedPR as $pr)
                                                <tr wire:key="selected-pr-{{ $pr['id'] }}">
                                                    <td
                                                        class="px-2 py-1 text-gray-900 dark:text-gray-100 whitespace-nowrap">
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
                                                        <button
                                                            wire:click.prevent="removeSelectedPR('{{ $pr['unique_key'] }}')"
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
                                            Showing {{ $this->SelectedPR->firstItem() }} to
                                            {{ $this->SelectedPR->lastItem() }} of
                                            {{ $this->SelectedPR->total() }} items
                                        </div>

                                        {{-- Column 2: Pagination (Center Aligned) --}}
                                        <nav role="navigation" aria-label="Pagination Navigation"
                                            class="flex justify-center items-center gap-3">

                                            {{-- Previous Button --}}
                                            <button wire:click.prevent="previousCustomPage('selectedPRPage')"
                                                @disabled($this->SelectedPR->onFirstPage())
                                                class="inline-flex items-center justify-center w-5 h-5 text-gray-600 hover:text-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:text-emerald-600 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-5">
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
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-5">
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
            @endif
            @if ($activeTab == 2)
                <div
                    class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
                    @foreach (collect($form['modes'])->values() as $modeIndex => $mode)
                        <div class="bg-white p-4 rounded-xl shadow border border-emerald-600 space-y-6">

                            {{-- Mode of Procurement Select --}}
                            <div class="flex justify-center">
                                <div class="bg-white p-4 rounded-xl border border-gray-200 inline-block">
                                    @php
                                        $isModeLocked =
                                            !$isEditing &&
                                            collect($mode['bid_schedules'] ?? [])->contains(
                                                fn($s) => !empty($s['bidding_result']) ||
                                                    !empty($s['ntf_bidding_result']),
                                            );
                                    @endphp

                                    <x-forms.select id="mode_of_procurement_{{ $modeIndex }}"
                                        label="Mode of Procurement"
                                        model="form.modes.{{ $modeIndex }}.mode_of_procurement_id" :form="$form"
                                        :options="$modeOfProcurements" optionValue="id" optionLabel="modeofprocurements"
                                        :required="false" :viewOnly="$viewOnlyTab2 || $isModeLocked" wireModifier="defer" />
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

            @if ($activeTab == 3)
                <div
                    class="bg-white p-4 rounded-xl shadow border border-gray-200 dark:bg-neutral-700 dark:border-neutral-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Awarding</h3>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">Your form components for awarding will go here.
                    </p>
                </div>
            @endif
        </div>

        <livewire:mode-of-procurement.select-modal :procurementType="$procurementType" :existing-lot-ids="$existingLotIds" :existing-item-ids="$existingItemIds" />

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
