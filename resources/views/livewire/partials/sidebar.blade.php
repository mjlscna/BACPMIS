<!-- Sidebar -->
<div id="navbar-collapse-with-animation" class="hs-overlay [--auto-close:lg] hs-overlay-open:translate-x-0
            transition-all duration-100 transform
            w-55 h-full
            hidden
            fixed inset-y-0 start-0 z-50  /* fixed & above all layers */
            bg-white border-e border-white
            lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
            dark:bg-neutral-800 dark:border-neutral-700" role="dialog" tabindex="-1" aria-label="Sidebar">

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
        <div class="flex-1 overflow-y-auto
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
                            {{ request()->routeIs('dashboard.page')
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white' }}
                            focus:outline-hidden dark:bg-neutral-700 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-white"
                            href="{{ route('dashboard.page') }}"><svg xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24" fill="currentColor" class="size-5">
                                <path
                                    d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                                <path
                                    d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <!-- Procurement -->
                    <li>
                        <a class="w-full flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                            {{ request()->routeIs('procurements.index')
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white' }}
                            focus:outline-hidden dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                            href="{{ route('procurements.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path
                                    d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" />
                                <path
                                    d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6H6v10.125A3.375 3.375 0 0 0 9.375 19.5H16.5v1.125c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V7.875C3 6.839 3.84 6 4.875 6Z" />
                            </svg>
                            Procurement
                        </a>
                    </li>
                    <!-- Mode of Procurement -->
                    <li>
                        <a class="w-full flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                            {{ request()->routeIs('modeofprocurements.index')
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white' }}
                            focus:outline-hidden dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                            href="{{ route('modeofprocurements.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path
                                    d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" />
                                <path
                                    d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6H6v10.125A3.375 3.375 0 0 0 9.375 19.5H16.5v1.125c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V7.875C3 6.839 3.84 6 4.875 6Z" />
                            </svg>
                            Mode of Procurement
                        </a>
                    </li>

                    <!-- Post Procurement -->
                    <li>
                        <a class="w-full flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                            {{ request()->routeIs('posts.index')
                                ? 'bg-emerald-600 text-white font-semibold'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white' }}
                            focus:outline-hidden dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                            href="{{ route('posts.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5">
                                <path
                                    d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" />
                                <path
                                    d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6H6v10.125A3.375 3.375 0 0 0 9.375 19.5H16.5v1.125c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V7.875C3 6.839 3.84 6 4.875 6Z" />
                            </svg>
                            Post Procurement
                        </a>
                    </li>

                </ul>
            </nav>
        </div>

        <!-- Fixed Admin Button -->
        <div class="ml-4 mr-4 p-1 bg-white border-t border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
            <a href="{{ url('/administrator') }}" target="_blank" rel="noopener noreferrer"
                class="w-full flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
        bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white
        focus:outline-hidden dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                    <path
                        d="M18.75 12.75h1.5a.75.75 0 0 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM12 6a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 6ZM12 18a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 18ZM3.75 6.75h1.5a.75.75 0 1 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM5.25 18.75h-1.5a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 0 1.5ZM3 12a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 3 12ZM9 3.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5ZM12.75 12a2.25 2.25 0 1 1 4.5 0 2.25 2.25 0 0 1-4.5 0ZM9 15.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                </svg>

                Administrator
            </a>

        </div>
    </div>
</div>
