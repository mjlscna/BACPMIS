<!-- Sidebar -->
<div id="navbar-collapse-with-animation"
    class="hs-overlay [--auto-close:lg] hs-overlay-open:translate-x-0
            transition-all duration-100 transform
            w-56 h-full
            hidden
            fixed inset-y-0 start-0 z-50  /* fixed & above all layers */
            bg-white border-e border-white
            lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
            dark:bg-neutral-800 dark:border-neutral-700"
    role="dialog" tabindex="-1" aria-label="Sidebar">

    <div class="flex flex-col h-full">
        <!-- Logo -->
        <!-- Sidebar Header / Logo -->
        <div class="bg-emerald-600 flex justify-center items-center text-center" style="height:155px;">
            <a href="#" aria-label="BACPMIS" class="block focus:outline-hidden focus:opacity-80">
                <h1 class="text-white font-bold leading-snug text-center">
                    <span class="text-2xl md:text-7xl">BAC </span><br>
                    <span class="text-s md:text-s">Procurement Monitoring</span><br>
                    <span class="text-s md:text-s">Information System</span>
                </h1>
            </a>
        </div>

        <!-- Scrollable Menu -->
        <div
            class="flex-1 overflow-y-auto
           [&::-webkit-scrollbar]:w-2
           [&::-webkit-scrollbar-thumb]:rounded-full
           [&::-webkit-scrollbar-track]:bg-gray-100
           [&::-webkit-scrollbar-thumb]:bg-gray-300
           dark:[&::-webkit-scrollbar-track]:bg-neutral-700
           dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">

            <nav class="hs-accordion-group p-3 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
                <ul class="flex flex-col space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a class="flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                    {{ request()->routeIs('dashboard')
                        ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                        : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}"
                            href="{{ route('dashboard') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path fill-rule="evenodd"
                                    d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z"
                                    clip-rule="evenodd" />
                                <path fill-rule="evenodd"
                                    d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z"
                                    clip-rule="evenodd" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Procurement -->
                    @can('view_any_procurement')
                        <li>
                            <a class="flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                        {{ request()->routeIs('procurements.index')
                            ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                            : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}"
                                href="{{ route('procurements.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5">
                                    <path
                                        d="M5.566 4.657A4.505 4.505 0 0 1 6.75 4.5h10.5c.41 0 .806.055 1.183.157A3 3 0 0 0 15.75 3h-7.5a3 3 0 0 0-2.684 1.657ZM2.25 12a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3v-6ZM5.25 7.5c-.41 0-.806.055-1.184.157A3 3 0 0 1 6.75 6h10.5a3 3 0 0 1 2.683 1.657A4.505 4.505 0 0 0 18.75 7.5H5.25Z" />
                                </svg>
                                Procurement
                            </a>
                        </li>
                    @endcan


                </ul>
            </nav>
        </div>


        <!-- Fixed Admin Button -->
        @can('view_any_administrator')
            <div class="ml-4 mr-4 p-1 bg-white border-t border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                <a href="{{ url('/administrator') }}" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                            {{ request()->is('administrator*')
                                ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path
                            d="M18.75 12.75h1.5a.75.75 0 0 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM12 6a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 6ZM12 18a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 18ZM3.75 6.75h1.5a.75.75 0 1 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM5.25 18.75h-1.5a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 0 1.5ZM3 12a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 3 12ZM9 3.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5ZM12.75 12a2.25 2.25 0 1 1 4.5 0 2.25 2.25 0 0 1-4.5 0ZM9 15.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                    </svg>
                    Administrator
                </a>
            </div>
        @endcan


    </div>
</div>
