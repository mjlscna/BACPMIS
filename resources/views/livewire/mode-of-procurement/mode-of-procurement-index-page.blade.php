<div x-data="{ showTypeModal: false }">
    <div
        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700 flex flex-col">

        <div
            class="sticky top-0 z-20 bg-white px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700 w-full">
            <div class="flex items-center gap-x-2">
                <div class="relative">
                    {{-- Updated placeholder --}}
                    <input type="text" wire:model.live="search" placeholder="Search by Ref No, PR No, or MOP..."
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
                        MOP
                    </button>
                @endcan
            </div>
        </div>
        <div class="overflow-auto flex-1">
            <table class="table-fixed w-full min-w-[1100px] divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-200 dark:bg-neutral-900">
                    <tr>
                        <th class="px-1 py-1 bg-gray-200 dark:bg-neutral-900 w-7"></th>
                        <th class="px-1 py-1 text-left text-xs text-black dark:text-white w-12">Ref Number</th>
                        <th class="px-1 py-1 text-center text-xs text-black dark:text-white w-28">Type</th>
                        <th class="px-1 py-1 text-center text-xs text-black dark:text-white w-48">Mode of Procurement
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($modes as $mode)
                        <tr x-data="{ expanded: false }"
                            class="bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700/50"
                            wire:key="mop-group-{{ $mode->id }}">

                            {{-- Actions Column --}}
                            <td class="px-2 py-2 text-center text-black dark:text-white">
                                {{-- ▼ Expand/Collapse Arrow --}}
                                <button @click="expanded = !expanded"
                                    class="inline-flex items-center justify-center size-7 rounded-full text-gray-600 hover:bg-gray-200 dark:text-neutral-400 dark:hover:bg-neutral-700 focus:outline-none transition">
                                    {{-- ► --}}
                                    <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        class="size-5 transition-transform duration-200">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{-- ▼ --}}
                                    <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        class="size-5 transition-transform duration-200 rotate-90">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                {{-- ⋮ Dropdown Menu --}}
                                <div x-data="{ open: false }" class="relative inline-flex" x-ref="menuWrapper">
                                    <button @click="open = !open" @click.away="open = false"
                                        class="inline-flex items-center justify-center size-7 rounded-full text-gray-600 hover:bg-gray-200 dark:text-neutral-400 dark:hover:bg-neutral-700">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
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
                                                            x-on:click="$dispatch('mode-of-procurement-view', { id: '{{ $mode->id }}' })"
                                                            type="button"
                                                            class="w-full flex items-center gap-x-2 text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-blue-500">
                                                            <x-heroicon-o-eye class="size-4" /> View
                                                        </button>
                                                    </li>
                                                @endcan
                                                @can('edit_procurement')
                                                    <li>
                                                        <a href="{{ route('mode-of-procurement.edit', $mode->id) }}"
                                                            @click="open = false"
                                                            class="w-full flex items-center gap-x-2 text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-neutral-700 text-amber-600">
                                                            <x-heroicon-o-pencil class="size-4" /> Edit
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </td>

                            {{-- Ref Number --}}
                            <td class="px-1 py-1 text-left text-sm text-black dark:text-white">
                                {{ $mode->ref_number }}
                            </td>

                            {{-- Type --}}
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                {{ $mode->procurable_type === 'perItem' ? 'Per Item' : 'Per Lot' }}
                            </td>

                            {{-- Mode of Procurement --}}
                            <td class="px-1 py-1 text-center text-sm text-black dark:text-white">
                                {{ $mode->modeOfProcurement?->modeofprocurements }}
                            </td>
                        </tr>

                        {{-- Collapsible Content --}}
                        <tr x-show="expanded" x-transition>
                            <td colspan="4" class="bg-gray-50 dark:bg-neutral-900 text-sm px-6 py-3">
                                @if ($mode->procurable_type === 'perItem')
                                    {{-- Table for mop_group_pr_item --}}
                                    <table class="w-full text-xs border border-gray-200 dark:border-neutral-700">
                                        <thead>
                                            <tr
                                                class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-gray-300">
                                                <th class="px-2 py-1 text-left">Item</th>
                                                <th class="px-2 py-1 text-left">Quantity</th>
                                                <th class="px-2 py-1 text-left">Unit Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($mode->mopGroupPrItems ?? [] as $item)
                                                <tr class="border-t border-gray-200 dark:border-neutral-700">
                                                    <td class="px-2 py-1">{{ $item->item_name ?? '-' }}</td>
                                                    <td class="px-2 py-1">{{ $item->quantity ?? '-' }}</td>
                                                    <td class="px-2 py-1">{{ $item->unit_cost ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    {{-- Table for mop_group_procurement --}}
                                    <table class="w-full text-xs border border-gray-200 dark:border-neutral-700">
                                        <thead>
                                            <tr
                                                class="bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-gray-300">
                                                <th class="px-2 py-1 text-left">Project Title</th>
                                                <th class="px-2 py-1 text-left">Approved Budget</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($mode->mopGroupProcurement ?? [] as $lot)
                                                <tr class="border-t border-gray-200 dark:border-neutral-700">
                                                    <td class="px-2 py-1">{{ $lot->project_title ?? '-' }}</td>
                                                    <td class="px-2 py-1">{{ $lot->approved_budget ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">
                                No Mode of Procurements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="flex flex-col items-center w-full p-2 border-t border-gray-200 dark:border-neutral-700">

            <div class="text-xs text-gray-500">
                @if ($modes->total() > 0)
                    Showing {{ $modes->firstItem() }} to {{ $modes->lastItem() }} of
                    {{ $modes->total() }} items
                @else
                    No items found
                @endif
            </div>

            <div>
                {{ $modes->links('vendor.pagination.tailwind') }}
            </div>

        </div>
    </div>

    {{-- Create Modal (No changes needed) --}}
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
                <p class="text-gray-700 dark:text-neutral-300">Please choose the type of procurement you are creating
                    mode of procurement for.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-neutral-800/50 rounded-b-2xl flex justify-center gap-x-4">
                <a href="{{ route('mode-of-procurement.create', ['type' => 'perLot']) }}"
                    class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-emerald-600 text-white hover:bg-emerald-700">
                    Per Lot
                </a>

                <a href="{{ route('mode-of-procurement.create', ['type' => 'perItem']) }}"
                    class="py-2 px-6 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-sky-600 text-white hover:bg-sky-700">
                    Per Item
                </a>

            </div>
        </div>
    </div>
</div>
