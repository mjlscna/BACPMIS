<div>
    <div class="max-w-[85rem] sm:px-6 mx-auto">
        <!-- Card -->
        <div class="flex flex-col items-center">
            <div class="overflow-x-auto w-full">
                <div class="min-w-full p-4 inline-block align-middle">
                    <div
                        class="max-w-6xl bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
                        <!-- Header -->
                        <div
                            class="sticky top-0 z-40 bg-white dark:bg-neutral-900 px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                            <div class="flex items-center gap-x-2">
                                <!-- Search Bar -->
                                <div class="relative">
                                    <input type="text" wire:model.debounce.500ms="search"
                                        placeholder="Search venues..."
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
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    Add Venue
                                </button>
                            </div>
                        </div>
                        <!-- End Header -->
                        <div class="overflow-y-auto max-h-[600px] relative">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-900 sticky top-0 z-40">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium sticky left-0 text-gray-500 uppercase dark:text-neutral-500">
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Name</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Slug</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Active</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($venues as $venue)
                                        <tr class="border-b border-gray-200 dark:border-neutral-700">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium sticky left-0 bg-white dark:bg-neutral-900 z-10">
                                                <button type="button" wire:click="openEditModal({{ $venue->id }})"
                                                    class="inline-flex items-center text-sm font-semibold rounded-lg border border-transparent text-emerald-600 hover:text-yellow-300 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 mr-2"
                                                    title="Edit">
                                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                                </button>
                                                <button type="button"
                                                    wire:click="confirmVenueRemoval({{ $venue->id }})"
                                                    class="inline-flex items-center text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:text-red-800 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:text-red-500 dark:hover:text-red-400"
                                                    title="Delete">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                {{ $venue->venue }}</td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $venue->slug }}</td>
                                            <td class="text-center">
                                                @if ($venue->is_active)
                                                    <x-heroicon-s-check-circle title="Active"
                                                        class="h-5 w-5 text-emerald-600 mx-auto" />
                                                @else
                                                    <x-heroicon-s-x-circle title="Inactive"
                                                        class="h-5 w-5 text-red-600 mx-auto" />
                                                @endif
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $venue->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                            {{ $venues->links('vendor.pagination.tailwind') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>
    <!-- End Table Section -->

    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-emerald-600/20 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg w-full max-w-lg mx-4 sm:mx-auto transition-all overflow-hidden max-h-[90vh]">
                <!-- Header -->
                <div
                    class="flex justify-between items-center p-1 border-gray-200 bg-emerald-600 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-white ml-2">{{ $editingId ? 'Edit Venue' : 'Add Venue' }}</h2>
                    <button wire:click="$set('showCreateModal', false)"
                        class="text-red-600 hover:text-red-700 dark:text-white dark:hover:text-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Form -->
                <div class="border px-4 py-2 border-gray-200 dark:border-neutral-700 max-h-[65vh] overflow-y-auto">
                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Venue</label>
                            <input type="text" wire:model.lazy="form.venue"
                                class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white"
                                required />
                            @error('form.venue')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Slug</label>
                            <input type="text" wire:model.defer="form.slug"
                                class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white bg-gray-100"
                                required disabled />
                            @error('form.slug')
                                <span class="text-red-600 text-xs">{{ $message ?? $errors->first('form.slug') }}</span>
                            @enderror
                        </div>
                        <div class="mb-4 flex items-center">
                            <label for="is_active_toggle"
                                class="block text-sm font-medium text-gray-700 dark:text-white mr-4">Is active</label>
                            <label for="venue-active-toggle"
                                class="relative inline-block w-15 h-8 cursor-pointer ml-2">
                                <input type="checkbox" id="venue-active-toggle" class="peer sr-only"
                                    wire:model.live="form.is_active">
                                <span
                                    class="absolute inset-0 bg-red-500 rounded-full transition-colors duration-200 ease-in-out peer-checked:bg-emerald-600"></span>
                                <span
                                    class="absolute top-1/2 start-0.5 -translate-y-1/2 size-7 bg-white rounded-full shadow-xs transition-transform duration-200 ease-in-out peer-checked:translate-x-full"></span>
                                <!-- Left Icon -->
                                <span
                                    class="absolute top-1/2 start-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-red-500 peer-checked:text-white">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6 6 18"></path>
                                        <path d="m6 6 12 12"></path>
                                    </svg>
                                </span>
                                <!-- Right Icon -->
                                <span
                                    class="absolute top-1/2 end-1.5 -translate-y-1/2 flex justify-center items-center size-5 text-gray-500 peer-checked:text-emerald-600">
                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </span>
                            </label>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-neutral-700 dark:text-white dark:border-neutral-600 dark:hover:bg-neutral-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                                {{ $editingId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
