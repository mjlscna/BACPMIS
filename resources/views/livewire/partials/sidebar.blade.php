<div>
    <!-- Sidebar -->
    <div id="navbar-collapse-with-animation" class="hs-overlay  [--auto-close:lg]
    hs-overlay-open:translate-x-0 transition-all duration-100 transform
    w-55 h-full
    hidden
    fixed inset-y-0 start-0 z-50
    bg-white border-e border-white
    lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
    dark:bg-neutral-800 dark:border-neutral-700" role="dialog" tabindex="-1" aria-label="Sidebar">
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
                                {{ request()->routeIs('dashboard.page') ? 'bg-emerald-600 text-white font-semibold ' : 'bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white ?>' }}
                                focus:outline-hidden focus:bg-gray-100
                                dark:bg-neutral-700 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-white"
                                href="{{ route('dashboard.page') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-6">
                                    <path
                                        d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                                    <path
                                        d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                                </svg>

                                Dashboard
                            </a>
                        </li>

                        <li>
                            <a class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg
                                {{ request()->routeIs('procurement.page') ? 'bg-emerald-600 text-white font-semibold' : 'bg-gray-100 font-semibold text-gray-800 hover:bg-emerald-600 hover:text-white ?>' }}
                                focus:outline-hidden focus:bg-gray-100
                                dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                                href="{{ route('procurement.page') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-6">
                                    <path
                                        d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" />
                                    <path
                                        d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6H6v10.125A3.375 3.375 0 0 0 9.375 19.5H16.5v1.125c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V7.875C3 6.839 3.84 6 4.875 6Z" />
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
                class="absolute bottom-0 left-0 w-full p-5 bg-white border-t border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                <ul class="flex flex-col space-y-1 mb-1">
                    <!-- Added mb-2 for spacing -->
                    <li>
                        <a class="flex items-center gap-x-3 py-2 px-1 text-sm rounded-lg w-full justify-center
        bg-gray-100 text-gray-800 font-semibold hover:bg-emerald-600 hover:text-white focus:outline-hidden focus:bg-gray-100
        dark:bg-neutral-800 dark:hover:bg-emerald-300 dark:focus:bg-neutral-700 dark:text-neutral-200"
                            href="{{ url('/administrator') }}" target="_blank">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="size-6">
                                <path fill-rule="evenodd"
                                    d="M12 6.75a5.25 5.25 0 0 1 6.775-5.025.75.75 0 0 1 .313 1.248l-3.32 3.319c.063.475.276.934.641 1.299.365.365.824.578 1.3.64l3.318-3.319a.75.75 0 0 1 1.248.313 5.25 5.25 0 0 1-5.472 6.756c-1.018-.086-1.87.1-2.309.634L7.344 21.3A3.298 3.298 0 1 1 2.7 16.657l8.684-7.151c.533-.44.72-1.291.634-2.309A5.342 5.342 0 0 1 12 6.75ZM4.117 19.125a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75h-.008a.75.75 0 0 1-.75-.75v-.008Z"
                                    clip-rule="evenodd" />
                                <path
                                    d="m10.076 8.64-2.201-2.2V4.874a.75.75 0 0 0-.364-.643l-3.75-2.25a.75.75 0 0 0-.916.113l-.75.75a.75.75 0 0 0-.113.916l2.25 3.75a.75.75 0 0 0 .643.364h1.564l2.062 2.062 1.575-1.297Z" />
                                <path fill-rule="evenodd"
                                    d="m12.556 17.329 4.183 4.182a3.375 3.375 0 0 0 4.773-4.773l-3.306-3.305a6.803 6.803 0 0 1-1.53.043c-.394-.034-.682-.006-.867.042a.589.589 0 0 0-.167.063l-3.086 3.748Zm3.414-1.36a.75.75 0 0 1 1.06 0l1.875 1.876a.75.75 0 1 1-1.06 1.06L15.97 17.03a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd" />
                            </svg>
                            Administrator
                        </a>
                    </li>

                </ul>

            </div>
        </div>
        <!-- ========== END MAIN CONTENT ========== -->
    </div>
</div>
