<div class="fixed top-0 inset-x-0 z-50 pt-28 ">
    <header
        class="sticky top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-50 w-full h-11 bg-emerald-600 border-b border-gray-200 text-sm py-2.5 lg:ps-55 dark:bg-neutral-800 dark:border-neutral-700">
        <nav class="px-4 sm:px-6 flex basis-full items-center w-full mx-auto">
            <div class="me-5 lg:me-0 lg:hidden">
                <!-- Logo -->
                <a class="flex-none rounded-md text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80"
                    href="#" aria-label="LMS">
                    <img src="" width="200" height="40"></img>
                </a>
                <!-- End Logo -->

                <div class="lg:hidden ms-1">

                </div>
            </div>

            <div class="w-full flex items-center justify-end ms-auto md:justify-between gap-x-1 md:gap-x-3">

                <div class="hidden md:block">
                    <!-- Search Input -->
                    <!--<img src="" width="200" height="50"></img>-->
                    <!-- End Search Input -->
                </div>

                <div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
                    <button @click="open = !open" @keydown.escape="open = false" :aria-expanded="open.toString()"
                        class="size-9.5 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:text-white"
                        aria-haspopup="menu" aria-label="Dropdown">
                        <img class="shrink-0 w-8 h-8 rounded-full" src="{{ $userPhoto }}" alt="Avatar">

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
            </div>
        </nav>
    </header>
    <!-- ========== END HEADER ========== -->
    <!-- ========== MAIN CONTENT ========== -->
    @php
        $segments = generate_breadcrumbs([
            'dashboard' => 'Dashboard',
            'procurements' => 'Procurements',
            'mode-of-procurement' => 'Mode of Procurement',
            'posts' => 'Posts Procurement',
            'create' => 'Create',
            'edit' => 'Edit',
            'view' => 'View',
        ]);
    @endphp

    <div class="h-8">
        <div
            class="sticky top-0 inset-x-0 z-10 bg-white border-y border-gray-200 px-2 sm:px-2 lg:px-4 lg:pl-55 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center py-1">
                <ol class="ms-3 flex items-center whitespace-nowrap">
                    @foreach ($segments as $index => $segment)
                        <li
                            class="flex items-center text-xs {{ $index === count($segments) - 1 ? 'font-semibold text-gray-800 dark:text-neutral-400' : 'text-gray-800 dark:text-neutral-400' }}">
                            <a href="{{ $segment['url'] }}" class="hover:underline">
                                {{ $segment['label'] }}
                            </a>
                            @if ($index < count($segments) - 1)
                                <svg class="shrink-0 mx-3 overflow-visible size-2.5 text-gray-400 dark:text-neutral-500"
                                    width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>

</div>
