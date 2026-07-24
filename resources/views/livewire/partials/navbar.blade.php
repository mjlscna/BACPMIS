<div class="fixed top-0 inset-x-0 z-60 pt-20 ">
    <header id="app-topbar"
        class=" top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-50 w-full h-11 bg-emerald-600 border-gray-200 text-sm py-2.5 dark:bg-emerald-600 ">
        <nav class="ps-2 pe-4 sm:pe-6 flex basis-full items-center w-full mx-auto gap-x-3">
            <!-- Hamburger Toggle (mobile only) -->
            <div class="lg:hidden">
                <button type="button"
                    class="size-9 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-white hover:bg-emerald-700 focus:outline-hidden"
                    data-hs-overlay="#app-sidebar" aria-controls="app-sidebar" aria-label="Toggle navigation">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- System Title -->
            <a href="{{ route('dashboard') }}" aria-label="PMIS"
                class="inline-flex flex-col justify-center leading-none focus:outline-hidden focus:opacity-80">
                <span class="text-center text-white font-bold text-base sm:text-lg leading-none">PMIS</span>
                <span class="text-white/90 text-[9px] leading-tight whitespace-nowrap">
                    Procurement Monitoring Information System
                </span>
            </a>

            <div class="flex items-center ms-auto gap-x-1 md:gap-x-3">

                <div class="flex items-center gap-x-2">
                    <!-- Dark Mode Toggle -->
                    <button type="button" onclick="toggleDarkMode()"
                        class="size-9 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-white hover:bg-emerald-700 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:hover:bg-neutral-700"
                        aria-label="Toggle Dark Mode">
                        <svg class="hidden dark:block size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg class="block dark:hidden size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <!-- End Dark Mode Toggle -->

                    <!-- User Dropdown -->
                    <div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
                        <button @click="open = !open" @keydown.escape="open = false" :aria-expanded="open.toString()"
                            class="size-9.5 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:text-white"
                            aria-haspopup="menu" aria-label="Dropdown">
                            <img class="shrink-0 w-8 h-8 rounded-full" src="{{ $userPhoto }}" alt="Avatar"
                                onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23ffffff\'%3E%3Cpath d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/%3E%3C/svg%3E';">
                        </button>

                        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-account">

                            <div class="py-3 px-5 bg-gray-100 rounded-t-lg dark:bg-neutral-700">
                                <p class="text-sm text-gray-500 dark:text-neutral-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    {{ session('user')['firstName'] . ' ' . session('user')['lastName'] }}</p>
                                </p>
                            </div>

                            <div class="p-1.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-red-50 focus:outline-none dark:hover:bg-neutral-700 dark:hover:text-red-400 dark:focus:bg-neutral-700 dark:focus:text-red-400">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-10V5m0 0a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h6a2 2 0 002-2v-1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End User Dropdown -->
                </div>
            </div>
        </nav>
    </header>
    <!-- ========== END HEADER ========== -->
</div>
