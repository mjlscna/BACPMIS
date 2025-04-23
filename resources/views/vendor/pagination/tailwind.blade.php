@if ($paginator->hasPages())
    <!-- Pagination -->
    <nav class="flex items-center justify-center mt-6 gap-x-1" aria-label="Pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" disabled aria-label="Previous"
                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-400 cursor-not-allowed dark:text-white/20">
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M15 18L9 12l6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="sr-only">Previous</span>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" aria-label="Previous"
                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10">
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M15 18L9 12l6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="sr-only">Previous</span>
            </a>
        @endif

        {{-- Page Info --}}
        <div class="flex items-center gap-x-1">
            <span
                class="min-h-9.5 min-w-9.5 flex justify-center items-center border border-gray-200 text-gray-800 py-2 px-3 text-sm rounded-lg dark:border-neutral-700 dark:text-white">
                {{ $paginator->currentPage() }}
            </span>
            <span
                class="min-h-9.5 flex justify-center items-center text-gray-500 py-2 px-1.5 text-sm dark:text-neutral-500">of</span>
            <span
                class="min-h-9.5 flex justify-center items-center text-gray-500 py-2 px-1.5 text-sm dark:text-neutral-500">
                {{ $paginator->lastPage() }}
            </span>
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" aria-label="Next"
                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10">
                <span class="sr-only">Next</span>
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M9 18l6-6-6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        @else
            <button type="button" disabled aria-label="Next"
                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-400 cursor-not-allowed dark:text-white/20">
                <span class="sr-only">Next</span>
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path d="M9 18l6-6-6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        @endif
    </nav>
    <!-- End Pagination -->
@endif
