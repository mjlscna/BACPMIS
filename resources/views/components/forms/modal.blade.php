@props([
    'title' => null,
    'size' => 'max-w-6xl', // you can override per usage
])

<div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white shadow-xl w-full {{ $size ?? 'max-w-2xl' }} rounded-2xl overflow-hidden">
        <!-- Modal Header -->
        <div
            class="flex justify-between items-center px-4 py-2
                bg-emerald-600 text-white font-semibold
                dark:bg-neutral-800 dark:text-neutral-200">
            <h2 class="text-lg font-semibold">{{ $title ?? 'Modal' }}</h2>
            <button @click="show = false" class="text-red-500 hover:text-red-700 font-bold">✕</button>
        </div>

        <!-- Modal Body -->
        <div class="max-h-[80vh] overflow-y-auto p-4">
            {{ $slot }}
        </div>
    </div>
</div>
