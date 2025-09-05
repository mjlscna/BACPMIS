@props([
'title' => null,
'size' => 'max-w-3xl', // default width
])

<div x-data="{ open: @entangle('showModal').defer }" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div @click.away="open = false" class="w-full {{ $size }} bg-white rounded-lg shadow-lg overflow-hidden"
        x-transition>
        <!-- Header -->
        <div class="flex justify-between items-center px-4 py-2 border-b">
            <h2 class="text-lg font-semibold">{{ $title }}</h2>
            <button @click="open = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
</div>
