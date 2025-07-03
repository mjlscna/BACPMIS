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
                                    <input type="text" wire:model.debounce.500ms="search" placeholder="Search users..."
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
                                    Create User
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
                                            Name
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Email
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Email Verified At
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 whitespace-nowrap">
                                            Created At
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr class="border-b border-gray-200 dark:border-neutral-700">
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium sticky left-0 bg-white dark:bg-neutral-900 z-10">
                                                <button type="button" wire:click="openEditModal({{ $user->id }})"
                                                    class="inline-flex items-center text-sm font-semibold rounded-lg border border-transparent text-emerald-600 hover:text-yellow-300 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:text-blue-500 dark:hover:text-blue-400 mr-2"
                                                    title="Edit">
                                                    <x-heroicon-o-pencil class="w-5 h-5" />
                                                </button>
                                                <button type="button"
                                                    wire:click="confirmUserRemoval({{ $user->id }})"
                                                    class="inline-flex items-center text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:text-red-800 focus:outline-none disabled:opacity-50 disabled:pointer-events-none dark:text-red-500 dark:hover:text-red-400"
                                                    title="Delete">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                {{ $user->name }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $user->email }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $user->email_verified_at?->format('d/m/Y H:i') }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $user->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                            {{ $users->links('vendor.pagination.tailwind') }}
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
                    <h2 class="text-lg font-semibold text-white ml-2">{{ $editingId ? 'Edit User' : 'Create User' }}
                    </h2>
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Name</label>
                            <input type="text" wire:model.defer="form.name"
                                class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white"
                                required />
                            @error('form.name')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Email Address</label>
                            <input type="email" wire:model.defer="form.email"
                                class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white"
                                required />
                            @error('form.email')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Email Verified
                                At</label>
                            <input type="date" wire:model.defer="form.email_verified_at"
                                class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white" />
                            @error('form.email_verified_at')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Password</label>
                            <input type="password" wire:model.defer="form.password"
                                class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white"
                                @if ($editingId) placeholder="Leave blank to keep current password" @endif
                                required />
                            @error('form.password')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
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
