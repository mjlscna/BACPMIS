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
                                            <td class="text-center relative">
                                                <div x-data="{ open: false }" class="inline-block">
                                                    <button @click="open = !open" @click.away="open = false"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-200 focus:outline-none ">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                            class="size-5 text-emerald-600">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                                        </svg>

                                                    </button>
                                                    <div x-show="open" x-transition
                                                        class="absolute left-14 top-1/2 -translate-y-1/2 z-50 min-w-max bg-white border border-gray-200 rounded shadow-lg"
                                                        style="display: none;">
                                                        <ul class="py-1 text-sm">
                                                            <li>
                                                                <button wire:click="view({{ $user->id }})"
                                                                    @click="open = false"
                                                                    class="w-full flex items-center gap-1 text-left px-4 py-2 hover:bg-gray-100 text-blue-500">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.5" stroke="currentColor"
                                                                        class="size-4 text-blue-500">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                    </svg>
                                                                    View
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button wire:click="openEditModal({{ $user->id }})"
                                                                    @click="open = false"
                                                                    class="w-full flex items-center gap-1 text-left px-4 py-2 hover:bg-gray-100 text-emerald-600">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.5" stroke="currentColor"
                                                                        class="size-4 text-emerald-600">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                                    </svg>

                                                                    Edit
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button wire:click="delete({{ $user->id }})"
                                                                    @click="open = false"
                                                                    class="w-full flex items-center gap-1 text-left px-4 py-2 hover:bg-gray-100 text-red-600">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.5" stroke="currentColor"
                                                                        class="size-4 text-red-600">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                    </svg>

                                                                    Delete
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Email
                                Address</label>
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
                            <div x-data="{ show: false, dummy: '••••••' }" class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model.defer="form.password"
                                    class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-neutral-900 dark:text-white pr-10"
                                    :value="$wire.editingId && !$wire.form.password ? '••••••' : $wire.form.password"
                                    @focus="if ($event.target.value === '••••••') { $event.target.value = ''; $wire.form.password = ''; }"
                                    autocomplete="new-password" required />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button" @click="show = !show"
                                        class="text-gray-500 dark:text-neutral-400 focus:outline-none">
                                        <template x-if="!show">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                fill="currentColor" class="size-6">
                                                <path
                                                    d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z" />
                                                <path
                                                    d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z" />
                                                <path
                                                    d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z" />
                                            </svg>
                                        </template>
                                        <template x-if="show">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            </div>
                            @error('form.password')
                                <span
                                    class="text-red-600 text-xs">{{ $message ?? $errors->first('form.password') }}</span>
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

    @if ($showViewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-emerald-600/20 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg w-full max-w-lg mx-4 sm:mx-auto transition-all overflow-hidden max-h-[90vh]">
                <!-- Header -->
                <div
                    class="flex justify-between items-center p-1 border-gray-200 bg-emerald-600 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold text-white ml-2">View User</h2>
                    <button wire:click="$set('showViewModal', false)"
                        class="text-red-600 hover:text-red-700 dark:text-white dark:hover:text-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Details -->
                <div class="border px-4 py-2 border-gray-200 dark:border-neutral-700 max-h-[65vh] overflow-y-auto">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Name</label>
                        <input type="text" value="{{ $viewData['name'] ?? '' }}" readonly
                            class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 bg-gray-100 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Email Address</label>
                        <input type="email" value="{{ $viewData['email'] ?? '' }}" readonly
                            class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 bg-gray-100 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Email Verified
                            At</label>
                        <input type="text"
                            value="{{ $viewData['email_verified_at'] ? \Carbon\Carbon::parse($viewData['email_verified_at'])->format('d/m/Y H:i') : '' }}"
                            readonly
                            class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 bg-gray-100 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Created At</label>
                        <input type="text"
                            value="{{ $viewData['created_at'] ? \Carbon\Carbon::parse($viewData['created_at'])->format('d/m/Y H:i') : '' }}"
                            readonly
                            class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 bg-gray-100 dark:bg-neutral-900 dark:text-white" />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" wire:click="$set('showViewModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-neutral-700 dark:text-white dark:border-neutral-600 dark:hover:bg-neutral-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
