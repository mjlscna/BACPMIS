<div class="space-y-5">
    @if (session()->has('success'))
        <div class="bg-teal-50 border-t-2 border-teal-500 rounded-lg p-4 dark:bg-teal-800/30" role="alert">
            <div class="flex">
                <div class="shrink-0">
                    <span
                        class="inline-flex justify-center items-center size-8 rounded-full border-4 border-teal-100 bg-teal-200 text-teal-800 dark:border-teal-900 dark:bg-teal-800 dark:text-teal-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor">
                            <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>
                </div>
                <div class="ms-3">
                    <h3 class="text-gray-800 font-semibold dark:text-white">Success</h3>
                    <p class="text-sm text-gray-700 dark:text-neutral-400">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border-s-4 border-red-500 p-4 dark:bg-red-800/30" role="alert">
            <div class="flex">
                <div class="shrink-0">
                    <span
                        class="inline-flex justify-center items-center size-8 rounded-full border-4 border-red-100 bg-red-200 text-red-800 dark:border-red-900 dark:bg-red-800 dark:text-red-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </span>
                </div>
                <div class="ms-3">
                    <h3 class="text-gray-800 font-semibold dark:text-white">Error</h3>
                    <p class="text-sm text-gray-700 dark:text-neutral-400">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
