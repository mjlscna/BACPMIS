<div>
    <div class="w-full">
        <!-- Card -->
        <div class="flex flex-col items-center">
            <div class="p-8 pr-10 inline-block align-middle w-full overflow-x-auto">
                <div
                    class="w-full bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">

                    <!-- Header -->
                    <div
                        class="sticky top-0 z-40 bg-white dark:bg-neutral-900 px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                        <div class="flex items-center gap-x-2">
                            <!-- Search Bar -->
                            <div class="relative">
                                <input type="text" wire:model.live="search" placeholder="Search Modes..."
                                    class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20"
                                    height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 21l-4.35-4.35" />
                                    <circle cx="10" cy="10" r="7" />
                                </svg>
                            </div>

                            <button x-on:click="$dispatch('open-mode-modal')"
                                class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="M12 5v14" />
                                </svg>
                                Mode
                            </button>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="overflow-y-auto max-h-[600px] relative">
                        <table class="min-w-[1500px] divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                                <tr>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase sticky left-0 z-30 bg-gray-50 dark:bg-neutral-800">
                                    </th>

                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase sticky left-[56px] z-20 bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        PR Number
                                    </th>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Procurement Program / Project
                                    </th>
                                    <th
                                        class="px-6 py-1 text-left text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Mode of Procurement
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                @foreach ($modes as $mode)
                                    <tr>
                                        <!-- Action Dropdown -->
                                        <td
                                            class="px-3 py-1 text-center text-emerald-600 sticky left-0 z-30 bg-white dark:bg-neutral-900">
                                            <div x-data="{ open: false }" class="relative inline-block"
                                                x-ref="menuWrapper">
                                                <button @click="open = !open" @click.away="open = false"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-200 dark:hover:bg-neutral-700 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-6">
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
                                                            <li>
                                                                <a href="#"
                                                                    class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-blue-500">
                                                                    <x-heroicon-o-eye class="w-4 h-4 text-blue-500" />
                                                                    View
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#"
                                                                    class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                                    <x-heroicon-o-pencil
                                                                        class="w-4 h-4 text-amber-600" /> Edit
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- PR Number -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $mode->procurement?->pr_number ?? '-' }}
                                        </td>

                                        <!-- Procurement Program / Project -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $mode->procurement?->procurement_program_project ?? '-' }}
                                        </td>

                                        <!-- Mode of Procurement -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-left text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $mode->modeOfProcurement?->name ?? '-' }}
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-0 m-0 py-4 -mt-2">
                        {{ $modes->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
                <!-- Procurement Modal -->
                <livewire:mode-of-procurement.mode-proc-select-modal />

            </div>
        </div>
    </div>
</div>
