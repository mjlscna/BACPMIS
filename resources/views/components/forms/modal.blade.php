@props([
    'title' => null,
    'size' => 'max-w-6xl',
])

<div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div class="bg-white shadow-xl w-full {{ $size }} rounded-2xl flex flex-col max-h-[90vh] overflow-hidden">
        <!-- Modal Header (fixed at top) -->
        <div
            class="flex justify-between items-center px-4 py-2
                bg-emerald-600 text-white font-semibold
                dark:bg-neutral-800 dark:text-neutral-200 flex-shrink-0">
            <h2 class="text-lg font-semibold">{{ $title ?? 'Modal' }}</h2>
            <button @click="show = false"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-red-500 hover:bg-gray-100 dark:bg-neutral-700 dark:text-red-500 dark:hover:bg-neutral-600 transition">
                ✕
            </button>
        </div>

        <!-- Modal Body (scrollable) -->
        <div class="flex-1 p-4 overflow-auto">
            {{ $slot }}
        </div>
    </div>
</div>
