@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-x-1" aria-label="Pagination">
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

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span
                    class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center text-sm rounded-lg text-gray-400 dark:text-white/20">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        {{-- Current Page Button --}}
                        <button type="button" disabled aria-current="page"
                            class="min-h-9.5 min-w-9.5 py-2 px-3 inline-flex justify-center items-center text-sm font-semibold rounded-lg bg-emerald-600 text-white">
                            {{ $page }}
                        </button>
                    @else
                        {{-- Regular Page Button --}}
                        <a href="{{ $url }}"
                            class="min-h-9.5 min-w-9.5 py-2 px-3 inline-flex justify-center items-center text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

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
@endif
