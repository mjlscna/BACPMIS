<div>
    <div
        class="m-4 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

        <div class="overflow-x-auto">

            <div
                class="sticky top-0 z-40 bg-white px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:bg-neutral-700 dark:border-neutral-700 min-w-[1500px] w-full">
                <div class="flex items-center gap-x-2">
                    <div class="relative">
                        <input type="text" wire:model.live="search" placeholder="Search Procurements..."
                            class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-900 dark:text-white dark:border-neutral-700" />
                        <svg class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20" height="20"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 21l-4.35-4.35" />
                            <circle cx="10" cy="10" r="7" />
                        </svg>
                    </div>
                    @can('create_procurement')
                        <button wire:click="promptEarlyProcurement"
                            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-hidden focus:bg-emerald-700">
                            <svg class="shrink-0 size-4" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg> Procurement
                        </button>
                    @endcan
                </div>
            </div>

            <div class="relative max-h-[600px] overflow-y-auto overflow-x-auto">
                <table class="min-w-[1500px] w-full divide-y divide-gray-200 dark:divide-neutral-700 table-auto">
                    {{-- The z-index on the header must be higher than the sticky columns for it to overlap correctly --}}
                    <thead class="bg-gray-200 dark:bg-neutral-900 sticky top-0 z-40">
                        <tr>
                            <th
                                class="px-1 text-center text-xs font-medium text-black dark:text-neutral-500 uppercase sticky left-0 z-30 bg-gray-200 dark:bg-neutral-900 w-14">
                            </th>
                            <th
                                class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase sticky left-[56px] z-20  whitespace-nowrap bg-gray-200 dark:bg-neutral-900 w-28">
                                PR Number</th>
                            <th
                                class="px-1 py-2 text-left text-xs font-medium text-black dark:text-white uppercase sticky left-[168px] z-10  whitespace-nowrap bg-gray-200 dark:bg-neutral-900 w-64">
                                Procurement Program / Project</th>
                            <th
                                class="px-6 py-1 text-center text-xs  font-medium text-black dark:text-white whitespace-nowrap">
                                Date Receipt</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                BAC Category</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                Division</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                Cluster / Committee</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                Category</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                Early Procurement</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                Source of Funds</th>
                            <th
                                class="px-6 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                ABC Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                        @foreach ($procurements as $procurement)
                            <tr>
                                <td
                                    class="px-1 text-center sticky left-0 z-30 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200 w-14">
                                    <!-- Alpine action dropdown -->
                                    <div x-data="{ open: false }" class="relative inline-block" x-ref="menuWrapper">
                                        <button @click="open = !open" @click.away="open = false"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-200 dark:hover:bg-neutral-700 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                            </svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition @click.away="open = false"
                                                class="absolute z-[9999] bg-white border border-gray-200 rounded shadow-lg dark:bg-neutral-800 dark:border-neutral-700"
                                                x-ref="dropdown" x-init="$watch('open', value => {
                                                    if (value) {
                                                        let rect = $refs.menuWrapper.getBoundingClientRect();
                                                        $refs.dropdown.style.top = (rect.top + window.scrollY) + 'px';
                                                        $refs.dropdown.style.left = (rect.right + 10 + window.scrollX) + 'px';
                                                    }
                                                })">
                                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                                    @can('view_procurement')
                                                        <li>
                                                            <button
                                                                x-on:click="$dispatch('open-procurement-view', { procID: '{{ $procurement->procID }}' })"
                                                                type="button"
                                                                class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-blue-500">
                                                                <x-heroicon-o-eye class="w-4 h-4 text-blue-500" />
                                                                View
                                                            </button>
                                                        </li>
                                                    @endcan
                                                    @can('update_procurement')
                                                        <li>
                                                            <a href="{{ route('procurements.edit', $procurement->procID) }}"
                                                                @click="open = false"
                                                                class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                                <x-heroicon-o-pencil class="w-4 h-4 text-amber-600" /> Edit
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </template>
                                    </div>
                                </td>

                                <td
                                    class="px-1 py-1 whitespace-nowrap text-center text-sm font-medium sticky left-[56px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-neutral-200 w-28">
                                    {{ $procurement->pr_number }}</td>
                                <td
                                    class="px-1 py-1 whitespace-normal break-words text-left text-sm font-medium sticky left-[168px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-neutral-200 w-64">
                                    {{ $procurement->procurement_program_project }}</td>

                                <td
                                    class="px-6 py-1 whitespace-nowrap text-center text-sm  text-black dark:text-neutral-200">
                                    {{ $procurement->date_receipt }}</td>
                                <td
                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-neutral-200">
                                    {{ $procurement->category?->bacType?->abbreviation ?? '' }}</td>
                                <td
                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-neutral-200">
                                    {{ $procurement->division->abbreviation }}</td>
                                <td
                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-neutral-200">
                                    {{ $procurement->clusterCommittee->clustercommittee }}</td>
                                <td
                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-neutral-200">
                                    {{ $procurement->category->category }}</td>

                                <td class="px-6 py-1 text-center text-sm text-black dark:text-neutral-200">
                                    @if ($procurement->early_procurement)
                                        <x-heroicon-s-check-circle title="Yes"
                                            class="h-5 w-5 text-emerald-600 mx-auto" />
                                    @else
                                        <x-heroicon-s-x-circle title="No" class="h-5 w-5 text-red-600 mx-auto" />
                                    @endif
                                </td>

                                <td
                                    class="px-6 py-1 whitespace-nowrap text-center text-sm text-black dark:text-neutral-200">
                                    {{ $procurement->fundSource ? $procurement->fundSource->fundsources : '' }}
                                </td>

                                <td class="px-6 py-1 text-center text-sm text-black dark:text-neutral-200 relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center text-gray-500">₱</span>
                                    <span>{{ number_format($procurement->abc ?? 0, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center w-full p-6 -mt-2 relative">
                {{-- Left text --}}
                <div class="text-sm text-gray-500 pl-2">
                    {{ $procurements->firstItem() }} to {{ $procurements->lastItem() }} of
                    {{ $procurements->total() }} items
                </div>

                {{-- Center pagination --}}
                <div class="absolute left-1/2 transform -translate-x-1/2 mb-5">
                    {{ $procurements->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </div>

        @if ($showEarlyPrompt)
            <div
                class="fixed inset-0 flex items-center justify-center bg-emerald-600/20  z-50 backdrop-blur-sm dark:bg-neutral-700/20">
                <div
                    class="bg-white rounded-xl shadow-lg p-6 w-96 text-center dark:bg-neutral-700 dark:border-neutral-700">
                    <h2 class="text-lg font-bold mb-4 text-black dark:text-white">Is this an Early
                        Procurement?
                    </h2>

                    <div class="flex justify-center gap-4">
                        <button wire:click="confirmEarly(false)" class="px-4 py-2 bg-red-500 text-white rounded-lg">
                            No
                        </button>
                        <button wire:click="confirmEarly(true)" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">
                            Yes
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ViewModal --}}

        <livewire:procurements.procurement-view-page />
    </div>
</div>
</div>
<!-- End Card -->
</div>
