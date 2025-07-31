<div>
    <!-- Sidebar -->
    <div id="navbar-collapse-with-animation"
        class="hs-overlay  [--auto-close:lg]
    hs-overlay-open:translate-x-0 transition-all duration-100 transform
    w-55 h-full
    hidden
    fixed inset-y-0 start-0 z-50
    bg-white border-e border-white
    lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
    dark:bg-neutral-800 dark:border-neutral-700"
        role="dialog" tabindex="-1" aria-label="Sidebar">
        <div class="relative flex flex-col h-full max-h-full">
            <div class="px-4 pt-4 flex items-center bg-emerald-600 pb-10">
                <!-- Logo -->
                <a class="flex-none text-center rounded-xl text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80"
                    href="#" aria-label="LMS">
                    <h1 class="text-1xl text-center font-bold text-white">BAC Procurement <br> Monitoring System</h1>
                </a>
                <!-- End Logo -->

                <div class="hidden lg:block ms-2">

                </div>
            </div>

            <!-- Content -->
            <div
                class="h-full overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                <nav class="hs-accordion-group p-3 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
                    <ul class="flex flex-col space-y-1">
                        <li>
                            <a class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg
                                {{ request()->routeIs('dashboard.page') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-emerald-600 hover:text-white ?>' }}
                                focus:outline-hidden focus:bg-gray-100
                                dark:bg-neutral-700 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-white"
                                href="{{ route('dashboard.page') }}">
                                <svg class="shrink-0 size-4 {{ request()->routeIs('dashboard.page') ? 'text-white' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                                Dashboard
                            </a>
                        </li>

                        <li>
                            <a class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg
                                {{ request()->routeIs('procurement.page') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-emerald-600 hover:text-white ?>' }}
                                focus:outline-hidden focus:bg-gray-100
                                dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                                href="{{ route('procurement.page') }}">
                                <svg class="shrink-0 size-4 {{ request()->routeIs('procurement.page') ? 'text-white' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                </svg>
                                Procurement
                            </a>
                        </li>
                        <!-- End Procurement Link -->

                    </ul>
                </nav>
            </div>
            <!-- End Content -->

            <!-- Admin Panel Button (static footer) -->
            <div
                class="absolute bottom-0 left-0 w-full p-4 bg-white border-t border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                <ul class="flex flex-col space-y-1 mb-2"> <!-- Added mb-2 for spacing -->
                    <li class="hs-accordion" id="admin-accordion" data-hs-accordion>
                        <button type="button"
                            class="hs-accordion-toggle flex items-center w-full gap-x-3.5 py-2 px-2.5 text-sm rounded-lg transition
                                {{ request()->routeIs('user.page') || request()->routeIs('venue.page') ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-800 hover:bg-emerald-600 hover:text-white' }}
                                focus:outline-none dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:text-neutral-200"
                            aria-controls="admin-accordion-content"
                            aria-expanded="{{ request()->routeIs('user.page') || request()->routeIs('venue.page') ? 'true' : 'false' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                            </svg>

                            Administrator
                            <svg class="ml-auto size-4 transition-transform hs-accordion-active:rotate-180"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="admin-accordion-content"
                            class="hs-accordion-content {{ request()->routeIs('user.page') || request()->routeIs('venue.page') ? 'block' : 'hidden' }} pl-6 mt-1 w-full overflow-hidden transition-[height] duration-300">
                            <ul class="flex flex-col space-y-1">
                                <!-- Users Menu Item -->
                                <li>
                                    <a class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg w-full justify-center
                    bg-gray-100 text-gray-800 hover:bg-emerald-600 hover:text-white focus:outline-hidden focus:bg-gray-100
                    dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                                        href="{{ url('/admin-panel') }}" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                        </svg>

                                        Admin Panel
                                    </a>
                                </li>
                                <!-- Venues Menu Item -->
                                {{-- <li>
                                    <a href="{{ route('venue.page') }}"
                                        class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg
                            {{ request()->routeIs('venue.page') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-emerald-600 hover:text-white' }}
                            focus:outline-none
                            dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:text-neutral-200">
                                        <svg class="shrink-0 size-4 {{ request()->routeIs('venue.page') ? 'text-white' : '' }}"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="10" width="18" height="8" rx="2" />
                                            <path d="M7 10V6a5 5 0 0 1 10 0v4" />
                                        </svg>
                                        Venues
                                    </a>
                                </li> --}}
                            </ul>
                        </div>
                    </li>
                </ul>

            </div>
        </div>
        <!-- ========== END MAIN CONTENT ========== -->
    </div>
</div>
