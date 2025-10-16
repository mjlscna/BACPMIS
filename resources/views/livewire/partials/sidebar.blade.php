<!-- Sidebar -->
<div id="navbar-collapse-with-animation"
    class="hs-overlay [--auto-close:lg] hs-overlay-open:translate-x-0
            transition-all duration-100 transform
            w-55 h-full
            hidden
            fixed inset-y-0 start-0 z-40  /* fixed & above all layers */
            bg-white border-e border-white
            lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
            dark:bg-neutral-800 dark:border-neutral-700"
    role="dialog" tabindex="-1" aria-label="Sidebar">

    <div class="flex flex-col h-full">
        <!-- Logo -->
        <!-- Sidebar Header / Logo -->
        <div class="bg-emerald-600 flex justify-center items-center text-center" style="height:124px;">
            <a href="#" aria-label="BACPMIS" class="block focus:outline-hidden focus:opacity-80">
                <h1 class="text-white font-bold leading-snug text-center">
                    <span class="text-2xl md:text-5xl">WV CHD </span><br>
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

                    @can('view_any_b::a::c::approved::p::r')
                        <li>
                            <a class="flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                        {{ request()->routeIs('bac-approved-pr.index')
                            ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                            : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}"
                                href="{{ route('bac-approved-pr.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-6">
                                    <path fill-rule="evenodd"
                                        d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z"
                                        clip-rule="evenodd" />
                                    <path fill-rule="evenodd"
                                        d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                BAC Approved PR
                            </a>
                        </li>
                    @endcan
                    @can('view_any_schedule::for::procurement')
                        <li>
                            <a class="flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                        {{ request()->routeIs('schedule-for-procurement.index')
                            ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                            : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}"
                                href="{{ route('schedule-for-procurement.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-6">
                                    <path
                                        d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                    <path fill-rule="evenodd"
                                        d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z"
                                        clip-rule="evenodd" />
                                </svg>


                                Schedule for PR
                            </a>
                        </li>
                    @endcan
                    <!-- Mode of Procurement -->
                    @can('view_any_mode::of::procurement')
                        <li>
                            <a
                                class="w-full flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                            {{ request()->routeIs('mode-of-procurement.index')
                                ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}"href="{{ route('mode-of-procurement.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-6">
                                    <path
                                        d="M6 3a3 3 0 0 0-3 3v2.25a3 3 0 0 0 3 3h2.25a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3H6ZM15.75 3a3 3 0 0 0-3 3v2.25a3 3 0 0 0 3 3H18a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3h-2.25ZM6 12.75a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h2.25a3 3 0 0 0 3-3v-2.25a3 3 0 0 0-3-3H6ZM17.625 13.5a.75.75 0 0 0-1.5 0v2.625H13.5a.75.75 0 0 0 0 1.5h2.625v2.625a.75.75 0 0 0 1.5 0v-2.625h2.625a.75.75 0 0 0 0-1.5h-2.625V13.5Z" />
                                </svg>

                                Mode of Procurement
                            </a>
                        </li>
                    @endcan
                    <!-- Post Procurement -->
                    {{-- <li>
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
                    </li> --}}


                </ul>
            </nav>
        </div>


        <!-- Fixed Admin Button -->
        @can('view_any_administrator')
            <div class="ml-4 mr-4 p-2 bg-white border-t border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                <a href="{{ url('/administrator') }}" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-x-2 py-2 px-2 text-sm rounded-lg
                            {{ request()->is('administrator*')
                                ? 'bg-emerald-600 text-white font-semibold dark:bg-emerald-600 dark:text-white'
                                : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-600 dark:hover:text-white' }}">
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
