@php
    $segments = generate_breadcrumbs([
        'dashboard' => 'Dashboard',
        'procurements' => 'Procurements',
        'mode-of-procurement' => 'Mode of Procurement',
        'bac-approved-pr' => 'BAC Approved PR',
        'pmu' => 'PMU',
        'create' => 'Create',
        'edit' => 'Edit',
        'view' => 'View',
        'pmr-cat-a' => 'PMR Category A',
        'pmr-cat-b' => 'PMR Category B',
        'bac' => 'BAC',
        'prs-received' => 'PRs Received (A)',
        'prs-received-b' => 'PRs Received (B)',
        'procurement-status' => 'Procurement Status',
    ]);
@endphp

<!-- ========== BREADCRUMB ========== -->
<div class="fixed top-[124px] inset-x-0 z-50 h-8">
    <div id="app-breadcrumb"
        class="h-full bg-white border-y border-gray-200 px-2 sm:px-2 lg:px-4 dark:bg-neutral-700 dark:border-neutral-700">
        <div class="flex items-center h-full py-1">
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
