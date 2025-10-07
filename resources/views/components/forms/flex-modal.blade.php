@props([
    'title' => null,
    'size' => 'max-w-6xl',
])

<div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
    class="fixed inset-0 z-50 flex items-start justify-center bg-emerald-700/50 p-4 overflow-y-auto">

    <div @click.away="show = false"
        class="bg-white shadow-xl w-full {{ $size }} rounded-2xl overflow-hidden dark:bg-neutral-800 my-8 flex flex-col"
        style="max-height: calc(100vh - 4rem);">

        {{-- Header --}}
        <div
            class="flex-shrink-0 flex justify-between items-center px-4 py-2 bg-emerald-600 text-white font-semibold dark:bg-neutral-900 dark:text-neutral-200">
            <h2 class="text-lg font-semibold">{{ $title ?? 'Modal' }}</h2>
            <button @click="show = false"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-red-500 hover:bg-gray-100 dark:bg-neutral-700 dark:text-red-500 dark:hover:bg-neutral-600 transition">
                ✕
            </button>
        </div>

        {{-- Content Area - Will handle both simple scrolling and complex flex layouts --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{ $slot }}
        </div>
    </div>
</div>
