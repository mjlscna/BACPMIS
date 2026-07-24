<!-- Sidebar -->
<div id="app-sidebar"
    class="hs-overlay [--auto-close:lg] hs-overlay-open:translate-x-0
            hidden
            fixed top-[124px] bottom-0 start-0 z-70
            bg-white
            lg:block lg:translate-x-0 lg:end-auto
            dark:bg-neutral-700"
    role="dialog" tabindex="-1" aria-label="Sidebar">

    <div class="flex flex-col h-full">
        <!-- Collapse Toggle (desktop only) -->
        <button onclick="toggleSidebar()" id="sidebar-collapse-btn"
            class="hidden lg:flex items-center justify-between w-full px-4 py-1.5
                   bg-gray-50 hover:bg-gray-100 dark:bg-neutral-800 dark:hover:bg-neutral-900
                   text-gray-400 dark:text-neutral-500 text-[9px] font-semibold tracking-widest uppercase
                   border-b border-gray-200 dark:border-neutral-600
                   transition-colors duration-200
                   focus:outline-none"
            title="Toggle Sidebar">
            <span class="sidebar-label opacity-70">Menu</span>
            <svg id="sidebar-toggle-icon" class="size-3 flex-shrink-0 transition-transform duration-300"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Scrollable Menu -->
        <div
            class="flex-1 overflow-y-auto overflow-x-hidden
           [&::-webkit-scrollbar]:w-1
           [&::-webkit-scrollbar-thumb]:rounded-full
           [&::-webkit-scrollbar-track]:bg-gray-100
           [&::-webkit-scrollbar-thumb]:bg-gray-300
           dark:[&::-webkit-scrollbar-track]:bg-neutral-700
           dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">

            <nav class="hs-accordion-group p-4 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
                <ul class="flex flex-col space-y-2">
                    <!-- Dashboard -->
                    <li>
                        <a wire:navigate
                            class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                    transition-all duration-200 border-l-4
                    {{ request()->routeIs('dashboard')
                        ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                        : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                            href="{{ route('dashboard') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-5 flex-shrink-0">
                                <path fill-rule="evenodd"
                                    d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z"
                                    clip-rule="evenodd" />
                                <path fill-rule="evenodd"
                                    d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Procurement -->
                    @can('view_any_procurement')
                        <li>
                            <a wire:navigate
                                class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                        transition-all duration-200 border-l-4
                        {{ request()->routeIs('procurements.*')
                            ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                            : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                href="{{ route('procurements.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5 flex-shrink-0">
                                    <path
                                        d="M5.566 4.657A4.505 4.505 0 0 1 6.75 4.5h10.5c.41 0 .806.055 1.183.157A3 3 0 0 0 15.75 3h-7.5a3 3 0 0 0-2.684 1.657ZM2.25 12a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3v-6ZM5.25 7.5c-.41 0-.806.055-1.184.157A3 3 0 0 1 6.75 6h10.5a3 3 0 0 1 2.683 1.657A4.505 4.505 0 0 0 18.75 7.5H5.25Z" />
                                </svg>
                                <span>Procurement</span>
                            </a>
                        </li>
                    @endcan

                    @can('view_any_b::a::c::approved::p::r')
                        <li>
                            <a wire:navigate
                                class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                        transition-all duration-200 border-l-4
                        {{ request()->routeIs('bac-approved-pr.*')
                            ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                            : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                href="{{ route('bac-approved-pr.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5 flex-shrink-0">
                                    <path fill-rule="evenodd"
                                        d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z"
                                        clip-rule="evenodd" />
                                    <path fill-rule="evenodd"
                                        d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>BAC Approved PR</span>
                            </a>
                        </li>
                    @endcan

                    @can('view_any_schedule::for::procurement')
                        <li>
                            <a wire:navigate
                                class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                        transition-all duration-200 border-l-4
                        {{ request()->routeIs('schedule-for-procurement.*')
                            ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                            : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                href="{{ route('schedule-for-procurement.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5 flex-shrink-0">
                                    <path
                                        d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                    <path fill-rule="evenodd"
                                        d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Schedule for PR</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Mode of Procurement -->
                    @can('view_any_mode::of::procurement')
                        <li>
                            <a wire:navigate
                                class="w-full flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                            transition-all duration-200 border-l-4
                            {{ request()->routeIs('mode-of-procurement.*')
                                ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                                : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                href="{{ route('mode-of-procurement.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5 flex-shrink-0">
                                    <path fill-rule="evenodd"
                                        d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z"
                                        clip-rule="evenodd" />
                                    <path
                                        d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z" />
                                </svg>
                                <span>Mode of PR</span>
                            </a>
                        </li>
                    @endcan

                    <!-- PMU -->
                    @can('view_any_pmu')
                        <li>
                            <a wire:navigate
                                class="w-full flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                            transition-all duration-200 border-l-4
                            {{ request()->routeIs('pmu.*')
                                ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                                : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                href="{{ route('pmu.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5">
                                    <path fill-rule="evenodd"
                                        d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z"
                                        clip-rule="evenodd" />
                                    <path fill-rule="evenodd"
                                        d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>PMU</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Supply -->
                    @can('view_any_supply')
                        <li>
                            <a wire:navigate
                                class="w-full flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                            transition-all duration-200 border-l-4
                            {{ request()->routeIs('supply.*')
                                ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                                : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                href="{{ route('supply.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-5 flex-shrink-0">
                                    <path
                                        d="M3.375 3C2.339 3 1.5 3.84 1.5 4.875v.75c0 1.036.84 1.875 1.875 1.875h17.25c1.035 0 1.875-.84 1.875-1.875v-.75C22.5 3.839 21.66 3 20.625 3H3.375Z" />
                                    <path fill-rule="evenodd"
                                        d="m3.087 9 .54 9.176A3 3 0 0 0 6.62 21h10.757a3 3 0 0 0 2.995-2.824L20.913 9H3.087Zm6.163 3.75A.75.75 0 0 1 10 12h4a.75.75 0 0 1 0 1.5h-4a.75.75 0 0 1-.75-.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Supply</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Reports Section -->
                    @can('view_reports')
                        <li class="pt-4 mt-4 border-t border-gray-200 dark:border-neutral-600">
                            <div
                                class="sidebar-section-header px-3 py-2 flex items-center gap-x-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-4">
                                    <path
                                        d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" />
                                    <path
                                        d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6H6v10.125A3.375 3.375 0 0 0 9.375 19.5H16.5v1.125c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V7.875C3 6.839 3.84 6 4.875 6Z" />
                                </svg>
                                <span>Reports</span>
                            </div>
                        </li>

                        <!-- PMR Reports -->
                        @can('view_reports')
                            <li>
                                <a wire:navigate href="{{ route('reports.pmr-cat-a') }}"
                                    class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                                    transition-all duration-200 border-l-4
                                    {{ request()->routeIs('reports.pmr-cat-a')
                                        ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                                        : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="currentColor" class="size-5 flex-shrink-0">
                                        <path fill-rule="evenodd"
                                            d="M1.5 5.625c0-1.036.84-1.875 1.875-1.875h17.25c1.035 0 1.875.84 1.875 1.875v12.75c0 1.035-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 18.375V5.625ZM21 9.375A.375.375 0 0 0 20.625 9h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5A.375.375 0 0 0 21 10.875v-1.5Zm0 3.75a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5a.375.375 0 0 0 .375-.375v-1.5Zm0 3.75a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5a.375.375 0 0 0 .375-.375v-1.5ZM10.875 18.75a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5ZM3.375 15h7.5a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375Zm0-3.75h7.5a.375.375 0 0 0 .375-.375v-1.5A.375.375 0 0 0 10.875 9h-7.5A.375.375 0 0 0 3 9.375v1.5c0 .207.168.375.375.375Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>PMR (CAT A)</span>
                                </a>
                            </li>
                            <li>
                                <a wire:navigate href="{{ route('reports.pmr-cat-b') }}"
                                    class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                                    transition-all duration-200 border-l-4
                                    {{ request()->routeIs('reports.pmr-cat-b')
                                        ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                                        : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="currentColor" class="size-5 flex-shrink-0">
                                        <path fill-rule="evenodd"
                                            d="M1.5 5.625c0-1.036.84-1.875 1.875-1.875h17.25c1.035 0 1.875.84 1.875 1.875v12.75c0 1.035-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 18.375V5.625ZM21 9.375A.375.375 0 0 0 20.625 9h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5A.375.375 0 0 0 21 10.875v-1.5Zm0 3.75a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5a.375.375 0 0 0 .375-.375v-1.5Zm0 3.75a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5a.375.375 0 0 0 .375-.375v-1.5ZM10.875 18.75a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375h7.5ZM3.375 15h7.5a.375.375 0 0 0 .375-.375v-1.5a.375.375 0 0 0-.375-.375h-7.5a.375.375 0 0 0-.375.375v1.5c0 .207.168.375.375.375Zm0-3.75h7.5a.375.375 0 0 0 .375-.375v-1.5A.375.375 0 0 0 10.875 9h-7.5A.375.375 0 0 0 3 9.375v1.5c0 .207.168.375.375.375Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>PMR (CAT B)</span>
                                </a>
                            </li>
                        @endcan

                        <!-- BAC Reports -->
                        @can('view_bac_reports')
                            <li class="hs-accordion {{ request()->routeIs(['reports.bac.prs-received', 'reports.bac.prs-received-b', 'reports.bac.procurement-status']) ? 'active' : '' }}"
                                id="reports-bac-accordion">
                                <button type="button"
                                    class="hs-accordion-toggle w-full flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                            transition-all duration-200 border-l-4
                            {{ request()->routeIs(['reports.bac.prs-received', 'reports.bac.prs-received-b', 'reports.bac.procurement-status'])
                                ? 'bg-emerald-50 text-emerald-600 border-l-emerald-600 dark:bg-emerald-600/30 dark:text-white dark:border-l-emerald-600'
                                : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}"
                                    aria-expanded="{{ request()->routeIs(['reports.bac.prs-received', 'reports.bac.prs-received-b', 'reports.bac.procurement-status']) ? 'true' : 'false' }}"
                                    aria-controls="reports-bac-accordion-child">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-5 flex-shrink-0">
                                        <path fill-rule="evenodd"
                                            d="M3 6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm4.5 7.5a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0v-2.25a.75.75 0 0 1 .75-.75Zm3.75-1.5a.75.75 0 0 0-1.5 0v4.5a.75.75 0 0 0 1.5 0V12Zm2.25-3a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0V9.75A.75.75 0 0 1 13.5 9Zm3.75-1.5a.75.75 0 0 0-1.5 0v9a.75.75 0 0 0 1.5 0v-9Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="flex-1 text-left">BAC</span>
                                    <svg class="sidebar-accordion-chevron hs-accordion-active:rotate-180 size-4 transition-transform duration-200"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div id="reports-bac-accordion-child"
                                    class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 {{ request()->routeIs(['reports.bac.prs-received', 'reports.bac.prs-received-b', 'reports.bac.procurement-status']) ? '' : 'hidden' }}"
                                    role="region" aria-labelledby="reports-bac-accordion">
                                    <ul class="ps-1 pt-1 space-y-1">
                                        <li>
                                            <a wire:navigate href="{{ route('reports.bac.prs-received') }}"
                                                class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                                        transition-all duration-200
                                        {{ request()->routeIs('reports.bac.prs-received')
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-300'
                                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-emerald-600/20' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-5 flex-shrink-0">
                                                    <path fill-rule="evenodd"
                                                        d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z"
                                                        clip-rule="evenodd" />
                                                    <path
                                                        d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" />
                                                </svg>
                                                <span>PR's Received (A)</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a wire:navigate href="{{ route('reports.bac.prs-received-b') }}"
                                                class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                                        transition-all duration-200
                                        {{ request()->routeIs('reports.bac.prs-received-b')
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-300'
                                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-emerald-600/20' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-5 flex-shrink-0">
                                                    <path fill-rule="evenodd"
                                                        d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z"
                                                        clip-rule="evenodd" />
                                                    <path
                                                        d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" />
                                                </svg>
                                                <span>PR's Received (B)</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a wire:navigate href="{{ route('reports.bac.procurement-status') }}"
                                                class="flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg
                                        transition-all duration-200
                                        {{ request()->routeIs('reports.bac.procurement-status')
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-300'
                                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-emerald-600/20' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-5 flex-shrink-0">
                                                    <path fill-rule="evenodd"
                                                        d="M3 6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm4.5 7.5a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0v-2.25a.75.75 0 0 1 .75-.75Zm3.75-1.5a.75.75 0 0 0-1.5 0v4.5a.75.75 0 0 0 1.5 0V12Zm2.25-3a.75.75 0 0 1 .75.75v6.75a.75.75 0 0 1-1.5 0V9.75A.75.75 0 0 1 13.5 9Zm3.75-1.5a.75.75 0 0 0-1.5 0v9a.75.75 0 0 0 1.5 0v-9Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span>Procurement Status</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        @endcan

                    @endcan
                </ul>
            </nav>
        </div>

        <!-- Fixed Admin Button -->
        @can('view_any_administrator')
            <div class="sidebar-bottom px-4 py-0.5 bg-white dark:bg-neutral-700">
                <a href="{{ url('/administrator') }}" target="_blank" rel="noopener noreferrer" title="Administrator"
                    class="sidebar-nav-item flex items-center gap-x-3 py-2 px-3 text-xs font-medium rounded-lg mt-4 mb-4
                            transition-all duration-200 border-l-4
                            {{ request()->is('administrator*')
                                ? 'bg-indigo-50 text-indigo-700 border-l-indigo-600 dark:bg-indigo-900/30 dark:text-white dark:border-l-indigo-400'
                                : 'bg-transparent text-gray-700 border-l-transparent hover:bg-gray-100 dark:text-white dark:hover:bg-emerald-600/50' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="size-5 flex-shrink-0">
                        <path
                            d="M18.75 12.75h1.5a.75.75 0 0 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM12 6a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 6ZM12 18a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 12 18ZM3.75 6.75h1.5a.75.75 0 1 0 0-1.5h-1.5a.75.75 0 0 0 0 1.5ZM5.25 18.75h-1.5a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 0 1.5ZM3 12a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 3 12ZM9 3.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5ZM12.75 12a2.25 2.25 0 1 1 4.5 0 2.25 2.25 0 0 1-4.5 0ZM9 15.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                    </svg>
                    <span>Administrator</span>
                </a>
            </div>
        @endcan
    </div>
</div>
