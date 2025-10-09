<div x-data="{ showTypeModal: false }">
    <div class="w-full">
        <div class="flex flex-col items-center">
            <div class="p-8 pr-10 inline-block align-middle w-full overflow-x-auto">
                <div
                    class="w-full bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">

                    <div
                        class="sticky top-0 z-40 bg-white dark:bg-neutral-900 px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                        <div class="flex items-center gap-x-2">
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Search by PR No. or Project..."
                                    class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20"
                                    height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 21l-4.35-4.35" />
                                    <circle cx="10" cy="10" r="7" />
                                </svg>
                            </div>
                            @can('create_b::a::c::approved::p::r')
                                <a href="{{ route('bac-approved-pr.create') }}" wire:navigate
                                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-hidden focus:bg-emerald-700">
                                    <svg class="shrink-0 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg> PR Document
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="relative w-full overflow-y-auto overflow-x-auto">
                        <table
                            class="min-w-[1500px] w-full divide-y divide-gray-200 dark:divide-neutral-700 table-auto">
                            {{-- The z-index on the header must be higher than the sticky columns for it to overlap correctly --}}
                            <thead class="bg-gray-200 dark:bg-neutral-900 sticky top-0 z-40">
                                <tr>
                                    <th
                                        class="px-1 text-center text-xs font-medium text-black dark:text-neutral-500 uppercase sticky left-0 z-30 bg-gray-200 dark:bg-neutral-900">
                                    </th>
                                    <th
                                        class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white uppercase sticky left-[56px] z-20  whitespace-nowrap bg-gray-200 dark:bg-neutral-900 w-28">
                                        PR Number</th>
                                    <th
                                        class="px-1 py-1 text-left text-xs font-medium text-black dark:text-white uppercase sticky left-[168px] z-10  whitespace-nowrap bg-gray-200 dark:bg-neutral-900 w-64">
                                        Procurement Program / Project</th>
                                    <th
                                        class="px-1 py-1 text-center text-xs  font-medium text-black dark:text-white whitespace-nowrap">
                                        Date Receipt</th>
                                    <th
                                        class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                        Division</th>
                                    <th
                                        class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                        Cluster / Committee</th>
                                    <th
                                        class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                        Source of Funds</th>
                                    <th
                                        class="px-1 py-1 text-center text-xs font-medium text-black dark:text-white whitespace-nowrap">
                                        ABC Amount</th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                {{-- Use @forelse to handle the case where there are no records --}}
                                @forelse ($approvedPrs as $pr)
                                    <tr>
                                        <td
                                            class="px-1 text-center sticky left-0 z-30 bg-white text-black dark:text-white dark:bg-neutral-800">
                                            <!-- Alpine action dropdown -->
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
                                                            @can('view_b::a::c::approved::p::r')
                                                                <li>
                                                                    <a href="{{ $pr->filepath }}" target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-green-600">

                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke-width="1.5" stroke="currentColor"
                                                                            class="size-4">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                                                        </svg>
                                                                        Document
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                            @can('update_b::a::c::approved::p::r')
                                                                <li>
                                                                    <a href="{{ route('bac-approved-pr.edit', $pr->procID) }}"
                                                                        @click="open = false"
                                                                        class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                                        <x-heroicon-o-pencil
                                                                            class="w-4 h-4 text-amber-600" /> Edit
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>

                                        {{-- CORRECTED: Use the eager-loaded relationship on the '$pr' object --}}
                                        <td
                                            class="px-1 py-1 whitespace-nowrap text-center text-sm font-medium sticky left-[56px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-white w-28">
                                            {{ $pr->procurement->pr_number ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-1 py-1 whitespace-normal break-words text-left text-sm font-medium sticky left-[168px] z-10 bg-white dark:bg-neutral-800 text-black dark:text-white w-64">
                                            {{ $pr->procurement->procurement_program_project ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-1 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                            {{ $pr->procurement->date_receipt ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-1 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                            {{ $pr->procurement->division->abbreviation ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-1 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                            {{ $pr->procurement->clusterCommittee->clustercommittee ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-1 py-1 whitespace-nowrap text-center text-sm text-black dark:text-white">
                                            {{ $pr->procurement->fundSource->fundsources ?? 'N/A' }}
                                        </td>
                                        <td class="px-1 py-1 text-center text-sm text-black dark:text-white relative">
                                            <span class="text-black dark:text-white">₱</span>
                                            <span>{{ number_format($pr->procurement->abc ?? 0, 2) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- This will be shown if '$approvedPrs' is empty --}}
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-gray-500 dark:text-neutral-400">
                                            No records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center w-full p-6 -mt-2 relative">
                        {{-- Left text --}}
                        <div class="text-sm text-gray-500 pl-2">
                            {{-- CORRECTED: Use the paginator variable '$approvedPrs' --}}
                            Showing {{ $approvedPrs->firstItem() }} to {{ $approvedPrs->lastItem() }} of
                            {{ $approvedPrs->total() }} items
                        </div>

                        {{-- Center pagination --}}
                        <div class="absolute left-1/2 transform -translate-x-1/2 mb-5">
                            {{ $approvedPrs->links('vendor.pagination.tailwind') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
