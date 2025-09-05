<div>
    <div class="w-full">
        <!-- Card -->
        <div class="flex flex-col items-center">
            <!-- Add items-center here -->
            <div class="p-8 pr-10 inline-block align-middle w-full overflow-x-auto">
                <div
                    class="w-full bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                    <!-- Header -->
                    <div
                        class="sticky top-0 z-40 bg-white dark:bg-neutral-900 px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                        <div class="flex items-center gap-x-2">
                            <!-- Search Bar -->
                            <div class="relative">
                                <input type="text" wire:model.live="search" placeholder="Search Procurements..."
                                    class="px-4 py-2 border border-gray-300 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-neutral-800 dark:text-white dark:border-neutral-700" />
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="absolute right-3 top-2.5 text-gray-500 dark:text-white" width="20"
                                    height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 21l-4.35-4.35" />
                                    <circle cx="10" cy="10" r="7" />
                                </svg>
                            </div>

                            <button wire:click="openCreateModal"
                                class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-hidden focus:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="M12 5v14" />
                                </svg> Procurement
                            </button>

                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="overflow-y-auto max-h-[600px] relative">
                        <table class="min-w-[1500px] divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                                <tr>
                                    <!-- Action Column -->
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase sticky left-0 z-30 bg-gray-50 dark:bg-neutral-800">
                                    </th>

                                    <!-- PR Number -->
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase sticky left-[56px] z-20 bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        PR Number
                                    </th>

                                    <!-- Procurement Program / Project -->
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase sticky left-[160px] z-10 bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Procurement Program / Project
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Date Receipt (Advance Copy)</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Date Receipt (Signed Copy)</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        RBAC / SBAC</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        DTRACK #</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        UniCode</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Division</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Cluster / Committee</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Category</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Venue(Specific)</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Venue(Province/HUC)</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Category / Venue</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Approved PPMP</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        APP Updated</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Immediate Date Needed</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Date Needed</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        PMO / End User</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Early Procurement</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Source of Funds</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        Expense Class</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        ABC Amount</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                        ABC <=> 50k</th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                @foreach ($procurements as $procurement)
                                <tr>
                                    <td class="sticky left-0 z-30 bg-white dark:bg-neutral-900 text-center px-4 py-2">
                                        <div x-data="{ open: false }" class="relative inline-block">
                                            <!-- Button -->
                                            <button @click="open = !open" @click.away="open = false"
                                                class="inline-flex items-center justify-center w-6 h-6 rounded-full hover:bg-gray-200 dark:hover:bg-neutral-700 focus:outline-none">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5 text-emerald-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                                </svg>
                                            </button>

                                            <!-- Dropdown -->
                                            <div x-show="open" x-transition
                                                class="absolute left-full top-1/2 -translate-y-1/2 z-50 min-w-max bg-white border border-gray-200 rounded shadow-lg dark:bg-neutral-800 dark:border-neutral-700"
                                                style="display: none;">
                                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                                    {{-- <li>
                                                        <button wire:click="openViewModal({{ $procurement->id }})"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-blue-500">
                                                            <x-heroicon-o-eye class="w-4 h-4 text-blue-500" /> View
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button wire:click="openEditModal({{ $procurement->id }})"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                            <x-heroicon-o-pencil class="w-4 h-4 text-amber-600" /> Edit
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button wire:click="openUpdateModal({{ $procurement->id }})"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-emerald-600">
                                                            <x-heroicon-o-arrow-path class="w-4 h-4 text-emerald-600" />
                                                            Update
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button wire:click="confirmDelete({{ $procurement->id }})"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-1 text-left px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-red-600">
                                                            <x-heroicon-o-trash class="w-4 h-4 text-red-600" /> Delete
                                                        </button>
                                                    </li> --}}
                                                </ul>
                                            </div>
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium sticky left-[56px] z-20 bg-white text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->pr_number }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium sticky left-[160px] z-10 bg-white text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->procurement_program_project }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->date_receipt_advance }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->date_receipt_signed }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->category?->bacType?->abbreviation ?? '' }}
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->dtrack_no }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->unicode }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->division->divisions }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->clusterCommittee->clustercommittee }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->category->category }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->venueSpecific?->name ?? '' }}
                                    </td>


                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->venueProvincesHUC?->province_huc ?? '' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->category_venue }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->approved_ppmp }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->app_updated }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->immediate_date_needed }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->date_needed }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->endUser?->endusers ?? 'No End-User Assigned' }}</td>
                                    <td class="text-center">
                                        @if ($procurement->early_procurement)
                                        <x-heroicon-s-check-circle title="Yes"
                                            class="h-5 w-5 text-emerald-600 mx-auto" />
                                        @else
                                        <x-heroicon-s-x-circle title="No" class="h-5 w-5 text-red-600 mx-auto" />
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->fundSource ? $procurement->fundSource->fundsources : '' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->expense_class }}</td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ '₱ ' . number_format($procurement->abc, 2) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $procurement->abc_50k }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <div class="p-0 m-0 py-4 -mt-2">
                        {{ $procurements->links('vendor.pagination.tailwind') }}
                    </div>

                </div>

                {{-- @if($showEarlyPrompt)
                <div class="fixed inset-0 flex items-center justify-center bg-emerald-600/20 z-50 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-lg p-6 w-96 text-center">
                        <h2 class="text-lg font-bold mb-4">Is this an Early Procurement?</h2>

                        <div class="flex justify-center gap-4">
                            <button wire:click="confirmEarly(false)" class="px-4 py-2 bg-red-500 text-white rounded-lg">
                                No
                            </button>
                            <button wire:click="confirmEarly(true)"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg">
                                Yes
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Procurement Modal -->
                @if($showCreateModal)
                <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-40">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-[600px]">
                        @include('livewire.procurement.procurement-modal')
                    </div>
                </div>
                @endif --}}


            </div>
        </div>
    </div>
    <!-- End Card -->
</div>
<!-- End Table Section -->
</div>
