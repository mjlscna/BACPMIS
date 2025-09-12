<div>
    <div class="w-full">
        <!-- Card -->
        <div class="flex flex-col items-center">
            <div class="p-8 pr-10 inline-block align-middle w-full overflow-x-auto">
                <div
                    class="inline-block bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">


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
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="overflow-y-auto max-h-[600px] relative">
                        <table class="min-w-[1000px] divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                                <tr>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase sticky left-0 z-30 bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        PR Number
                                    </th>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Procurement Program / Project
                                    </th>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Procurement Stage
                                    </th>
                                    <th
                                        class="px-6 py-1 text-center text-xs font-medium text-gray-500 dark:text-neutral-500 uppercase bg-gray-50 dark:bg-neutral-800 whitespace-nowrap">
                                        Division
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="bg-white divide-y divide-gray-200 dark:bg-neutral-800 dark:divide-neutral-700">
                                @foreach ($procurements as $procurement)
                                    <tr>
                                        <!-- PR Number -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200 sticky left-0 z-30 bg-white dark:bg-neutral-900">
                                            {{ $procurement->pr_number ?? '-' }}
                                        </td>

                                        <!-- Procurement Program / Project -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $procurement->procurement_program_project ?? '-' }}
                                        </td>

                                        <!-- Type -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $procurement->procurement_type === 'perItem' ? 'Per Item' : 'Per Lot' }}
                                        </td>

                                        <!-- Procurement Stage -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $procurement->prLotPrstages->last()?->procurementStage?->procurementstage }}
                                        </td>

                                        <!-- Division -->
                                        <td
                                            class="px-6 py-1 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $procurement->division->abbreviation }}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-0 m-0 py-4 -mt-2">
                        {{ $procurements->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
