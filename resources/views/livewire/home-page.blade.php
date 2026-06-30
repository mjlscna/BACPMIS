<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-4 border border-gray-100 dark:border-neutral-700">
        <div class="flex flex-wrap items-center gap-3">
            <div class="bg-emerald-500/10 p-2.5 rounded-xl">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 13.414V19a1 1 0 01-.293.707l-2 2A1 1 0 0111 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
            </div>

            <!-- Year (Date of Receipt) -->
            <div class="relative flex-1 min-w-[9rem] sm:flex-none">
                <span
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <select wire:model.live="selectedYear" aria-label="Filter by year"
                    class="appearance-none w-full sm:min-w-[10rem] pl-10 pr-9 py-2.5 rounded-xl border-2 border-gray-200 dark:border-neutral-600 bg-gray-50 dark:bg-neutral-800 text-sm font-semibold text-gray-700 dark:text-gray-100 cursor-pointer transition-all hover:border-emerald-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="all">All Years</option>
                    @foreach ($availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </div>

            <!-- Quarter (Date of Receipt) -->
            <div class="relative flex-1 min-w-[9rem] sm:flex-none">
                <span
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <select wire:model.live="selectedQuarter" aria-label="Filter by quarter"
                    @disabled($selectedYear === 'all')
                    class="appearance-none w-full sm:min-w-[10rem] pl-10 pr-9 py-2.5 rounded-xl border-2 border-gray-200 dark:border-neutral-600 bg-gray-50 dark:bg-neutral-800 text-sm font-semibold text-gray-700 dark:text-gray-100 cursor-pointer transition-all hover:border-amber-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="all">All Quarters</option>
                    <option value="1">1st Quarter</option>
                    <option value="2">2nd Quarter</option>
                    <option value="3">3rd Quarter</option>
                    <option value="4">4th Quarter</option>
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </div>

            <!-- Division -->
            <div class="relative flex-1 min-w-[10rem] sm:flex-none">
                <span
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <select wire:model.live="selectedDivision" aria-label="Filter by division"
                    class="appearance-none w-full sm:min-w-[13rem] pl-10 pr-9 py-2.5 rounded-xl border-2 border-gray-200 dark:border-neutral-600 bg-gray-50 dark:bg-neutral-800 text-sm font-semibold text-gray-700 dark:text-gray-100 cursor-pointer transition-all hover:border-purple-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30">
                    <option value="all">All Divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}">
                            {{ $division->abbreviation ?? $division->divisions }}
                        </option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </div>
        </div>
    </div>

    <!-- Hero Summary Section -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Total Procurements Card -->
        <div
            class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold opacity-90">Total Procurements</h3>
                </div>

                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-6xl font-bold mb-2">{{ $summaryStats['total'] }}</p>
                        <p class="text-sm opacity-80">Total Procurements</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-white/20">
                    <p class="text-sm opacity-80 mb-2">Total ABC Value</p>
                    <p class="text-3xl font-bold">₱{{ $summaryStats['totalAbc'] }}</p>
                </div>
            </div>
        </div>

        <!-- BAC Categories Card -->
        <div
            class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-6 border border-gray-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-blue-500/10 p-3 rounded-xl">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">BAC Categories</h3>
            </div>

            <div class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                @foreach ($summaryStats['bacCategories'] as $bac)
                    <div
                        class="group bg-gradient-to-r from-gray-50 to-white dark:from-neutral-800 dark:to-neutral-800 rounded-xl p-4 hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-neutral-600">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex-1 pr-3">
                                {{ $bac['fullName'] }}
                            </span>
                            <span
                                class="bg-blue-500 text-white text-sm font-bold px-3 py-1 rounded-lg min-w-[3rem] text-center">
                                {{ $bac['count'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">ABC:</span>
                            <span
                                class="text-lg font-bold text-blue-600 dark:text-blue-400">₱{{ $bac['totalAbc'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Division ABC Breakdown Card -->
        <div
            class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-6 border border-gray-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-purple-500/10 p-3 rounded-xl">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Division Breakdown</h3>
            </div>

            <div class="space-y-2 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                @foreach ($summaryStats['divisionAbc'] as $div)
                    <div
                        class="flex items-center justify-between bg-gradient-to-r from-purple-50 to-white dark:from-neutral-800 dark:to-neutral-800 rounded-lg px-4 py-3 hover:shadow-sm transition-all border border-purple-100 dark:border-neutral-600">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate max-w-[60%]">
                            {{ $div['abbreviation'] }}
                        </span>
                        <span class="text-base font-bold text-purple-600 dark:text-purple-400 whitespace-nowrap">
                            ₱{{ $div['totalAbc'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Division Cards Section -->
    <div class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-6 border border-gray-100 dark:border-neutral-700">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-emerald-500/10 p-3 rounded-xl">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Divisions Overview</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
            @foreach ($divisionCounts as $div)
                <div wire:click="selectDivision({{ $div['id'] }})"
                    class="group relative bg-gradient-to-br from-gray-50 to-white dark:from-neutral-800 dark:to-neutral-800 rounded-xl p-5 border-2 border-gray-200 dark:border-neutral-600 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-lg transition-all duration-300 cursor-pointer">

                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg flex items-center justify-center transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                <span class="text-2xl font-bold text-white">{{ $div['count'] }}</span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 mt-1">
                            <p class="text-sm font-bold text-gray-800 dark:text-gray-100 leading-tight line-clamp-3">
                                {{ $div['name'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Cluster Committee Breakdown -->
    @if ($selectedDivisionId)
        <div
            class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-6 border border-gray-100 dark:border-neutral-700 animate-fade-in">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-500/10 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                            {{ $selectedDivisionName }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cluster/Committee Breakdown</p>
                    </div>
                </div>
                <button wire:click="selectDivision({{ $selectedDivisionId }})"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($clusterCommitteeCounts as $cluster)
                    <div
                        class="bg-gradient-to-br from-indigo-50 to-white dark:from-neutral-700 dark:to-neutral-700 rounded-xl p-5 border-2 border-indigo-100 dark:border-neutral-600 hover:border-indigo-500 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <p class="text-sm font-bold text-gray-800 dark:text-gray-100 flex-1 pr-3 line-clamp-2">
                                {{ $cluster['name'] }}
                            </p>
                            <span
                                class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg text-white font-bold text-lg flex items-center justify-center shadow-md">
                                {{ $cluster['count'] }}
                            </span>
                        </div>
                        <div class="pt-3 border-t border-indigo-200 dark:border-neutral-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-500 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                                <span class="text-xs text-gray-600 dark:text-gray-400">ABC:</span>
                                <span class="text-sm font-bold text-purple-600 dark:text-purple-400 truncate">
                                    ₱{{ $cluster['totalAbc'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Category & Fund Source Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Procurement by Category -->
        <div
            class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-6 border border-gray-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-orange-500/10 p-3 rounded-xl">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Procurement by Category</h3>
            </div>
            @if ($categoryCounts->isNotEmpty())
                <div class="h-80" wire:ignore x-data="{
                    chart: null,
                    data: @js($categoryCounts),
                    colors: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#EF4444', '#14B8A6', '#F43F5E', '#6366F1', '#A855F7', '#22D3EE', '#FACC15']
                }" x-init="$nextTick(() => {
                    if (!window.Chart || !data.length) return;
                    const isDark = document.documentElement.classList.contains('dark');
                    chart = new window.Chart($refs.cCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: data.map(d => d.name),
                            datasets: [{
                                data: data.map(d => d.count),
                                backgroundColor: colors,
                                borderColor: isDark ? '#1F2937' : '#fff',
                                borderWidth: 3,
                                hoverOffset: 15
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: window.innerWidth < 640 ? 'bottom' : 'right',
                                    labels: {
                                        color: isDark ? '#F3F4F6' : '#374151',
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 14,
                                        font: { size: 11, weight: '600' },
                                        generateLabels(c) {
                                            return c.data.labels.map((l, i) => ({
                                                text: l + ': ' + c.data.datasets[0].data[i],
                                                fillStyle: c.data.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            }));
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label(ctx) {
                                                const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                return ctx.label + ': ' + ctx.parsed + ' (' + (ctx.parsed / t * 100).toFixed(1) + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                })">
                    <canvas x-ref="cCanvas"></canvas>
                </div>
            @else
                <div class="h-80 flex items-center justify-center">
                    <p class="text-gray-400 dark:text-gray-500">No category data available</p>
                </div>
            @endif
        </div>

        <!-- Procurement by Fund Source -->
        <div
            class="bg-white dark:bg-neutral-700 rounded-2xl shadow-xl p-6 border border-gray-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-violet-500/10 p-3 rounded-xl">
                    <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Procurement by Fund Source</h3>
            </div>
            @if ($fundSourceCounts->isNotEmpty())
                <div class="h-80" wire:ignore x-data="{
                    chart: null,
                    data: @js($fundSourceCounts),
                    colors: ['#8B5CF6', '#A78BFA', '#C4B5FD', '#7C3AED', '#6D28D9', '#DDD6FE', '#5B21B6', '#4C1D95', '#EDE9FE', '#F5F3FF']
                }" x-init="$nextTick(() => {
                    if (!window.Chart || !data.length) return;
                    const isDark = document.documentElement.classList.contains('dark');
                    chart = new window.Chart($refs.fCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: data.map(d => d.name),
                            datasets: [{
                                data: data.map(d => d.count),
                                backgroundColor: colors,
                                borderColor: isDark ? '#1F2937' : '#fff',
                                borderWidth: 3,
                                hoverOffset: 15
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: window.innerWidth < 640 ? 'bottom' : 'right',
                                    labels: {
                                        color: isDark ? '#F3F4F6' : '#374151',
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 14,
                                        font: { size: 11, weight: '600' },
                                        generateLabels(c) {
                                            return c.data.labels.map((l, i) => ({
                                                text: l + ': ' + c.data.datasets[0].data[i],
                                                fillStyle: c.data.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            }));
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label(ctx) {
                                                const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                return ctx.label + ': ' + ctx.parsed + ' (' + (ctx.parsed / t * 100).toFixed(1) + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                })">
                    <canvas x-ref="fCanvas"></canvas>
                </div>
            @else
                <div class="h-80 flex items-center justify-center">
                    <p class="text-gray-400 dark:text-gray-500">No fund source data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        /* Responsive breakpoints */
        @media (min-width: 475px) {
            .xs\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 639px) {
            .responsive-text {
                font-size: 0.875rem;
            }
        }
    </style>

</div>
