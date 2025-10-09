<div x-data="{ showTypeModal: false }">
    <div
        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden
           dark:bg-neutral-800 dark:border-neutral-700 dark:shadow-sm dark:shadow-neutral-400/50">
        <div
            class="sticky top-0 z-40 bg-white px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700 w-full">
            <div class="flex items-center gap-x-2">
                <!-- Search Bar -->
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Search Modes..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20" height="20"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 21l-4.35-4.35" />
                        <circle cx="10" cy="10" r="7" />
                    </svg>
                </div>

                @can('create_mode::of::procurement')
                    <button type="button" @click="showTypeModal = true"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        IB
                    </button>
                @endcan
            </div>
        </div>
        <!-- End Header -->

        <!-- Table -->
        <div class="relative w-full overflow-y-auto overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                    <tr>
                        {{-- Column 1: Actions (Removed right padding) --}}
                        <th scope="col"
                            class="sticky left-0 z-30 bg-gray-50 dark:bg-neutral-900 w-14 pl-2 pr-0 py-3">
                            <span class="sr-only">Actions</span>
                        </th>

                        {{-- Column 2: PR Number (Removed left padding and aligned left) --}}
                        <th scope="col"
                            class="sticky left-[56px] z-20 bg-gray-50 dark:bg-neutral-900 w-24 pr-2 pl-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase whitespace-nowrap">
                            PR Number
                        </th>

                        {{-- Column 3: Project Name (Updated left position) --}}
                        <th scope="col"
                            class="sticky left-[152px] z-10 bg-gray-50 dark:bg-neutral-900 w-80 px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase">
                            Procurement Program / Project
                        </th>

                        {{-- Type Column --}}
                        <th scope="col"
                            class="w-24 px-2 py-3 text-center text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase whitespace-nowrap">
                            Type
                        </th>

                        {{-- Mode of Procurement Column --}}
                        <th scope="col"
                            class="w-64 px-2 py-3 text-center text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase whitespace-nowrap">
                            Mode of Procurement
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @foreach ($modes as $mode)
                        <tr class="bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700/50">
                            {{-- Column 1: Actions (Removed right padding) --}}
                            <td class="sticky left-0 z-10 bg-white dark:bg-neutral-800 w-14 pl-2 pr-0 py-3">
                                <div x-data="{ open: false }" class="relative inline-flex" x-ref="menuWrapper">
                                    <button @click="open = !open" @click.away="open = false"
                                        class="inline-flex items-center justify-center size-8 rounded-full text-gray-600 hover:bg-gray-200 dark:text-neutral-400 dark:hover:bg-neutral-700 focus:outline-none">
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                        </svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open" x-transition @click.away="open = false"
                                            class="absolute z-[9999] bg-white border border-gray-200 rounded-md shadow-lg dark:bg-neutral-800 dark:border-neutral-700"
                                            x-ref="dropdown" x-init="$watch('open', value => {
                                                if (value) {
                                                    let rect = $refs.menuWrapper.getBoundingClientRect();
                                                    $refs.dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                                                    $refs.dropdown.style.left = (rect.left + window.scrollX) + 'px';
                                                }
                                            })">
                                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                                @can('view_procurement')
                                                    <li>
                                                        <button
                                                            x-on:click="$dispatch('mode-of-procurement-view', { procID: '{{ $mode->procID }}' })"
                                                            type="button"
                                                            class="w-full flex items-center gap-x-2 text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-blue-500">
                                                            <x-heroicon-o-eye class="size-4" />
                                                            View
                                                        </button>
                                                    </li>
                                                @endcan
                                                @can('update_procurement')
                                                    <li>
                                                        <a href="{{ route('mode-of-procurement.edit', $mode->procID) }}"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-x-2 text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                            <x-heroicon-o-pencil class="size-4" />
                                                            Edit
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </td>

                            {{-- Column 2: PR Number (Removed left padding and aligned left) --}}
                            <td
                                class="sticky left-[56px] z-10 bg-white dark:bg-neutral-800 w-24 pr-2 pl-0 py-3 whitespace-nowrap text-left text-sm font-medium text-gray-800 dark:text-neutral-200">
                                {{ $mode->procurement?->pr_number }}
                            </td>

                            {{-- Column 3: Project Name (This column will wrap text) --}}
                            <td
                                class="sticky left-[152px] z-10 bg-white dark:bg-neutral-800 w-80 px-2 py-3 whitespace-normal break-words text-left text-sm text-gray-800 dark:text-neutral-200">
                                {{ $mode->procurement?->procurement_program_project }}
                            </td>

                            {{-- Type Column --}}
                            <td class="w-24 px-2 py-2 text-center text-sm text-black dark:text-white whitespace-nowrap">
                                {{ $mode->procurement?->procurement_type === 'perLot' ? 'Per Lot' : 'Per Item' }}
                            </td>

                            {{-- Mode of Procurement Column --}}
                            <td
                                class="w-64 px-2 py-3 text-center text-sm text-gray-800 dark:text-neutral-200 whitespace-nowrap">
                                {{ $mode->modeOfProcurement?->modeofprocurements }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center w-full p-6 -mt-2 relative">
            {{-- Left text --}}
            <div class="text-sm text-gray-500 pl-2">
                {{ $modes->firstItem() }} to {{ $modes->lastItem() }} of
                {{ $modes->total() }} items
            </div>

            {{-- Center pagination --}}
            <div class="absolute left-1/2 transform -translate-x-1/2 mb-5">
                {{ $modes->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    <div @keydown.escape.window="showTypeModal = false" x-show="showTypeModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="showTypeModal = false"
            class="bg-white shadow-xl w-full max-w-md rounded-2xl dark:bg-neutral-800">
            <div
                class="flex justify-between items-center px-4 py-2 bg-emerald-600 text-white font-semibold dark:bg-neutral-900 rounded-t-2xl">
                <h2 class="text-lg font-semibold">Select Procurement Type</h2>
                <button @click="showTypeModal = false"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-white/70 hover:text-white transition">✕</button>
            </div>
            <div class="p-6 text-center">
                <p class="text-gray-700 dark:text-neutral-300">Please choose the type of procurement you are creating an
                    Invitation to Bid (IB) for.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-neutral-800/50 rounded-b-2xl flex justify-center gap-x-4">
                <a href="{{ route('mode-of-procurement.create', ['type' => 'perLot']) }}"
                    class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">Per
                    Lot</a>
                <a href="{{ route('mode-of-procurement.create', ['type' => 'perItem']) }}"
                    class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-sky-600 text-white hover:bg-sky-700">Per
                    Item</a>
            </div>
        </div>
    </div>
</div>
