<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'PMIS' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/DOH_Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles()

    <!-- Custom styles -->
    <style>
        /* Ensure SweetAlert2 appears above modals */
        .swal-z-index-max {
            z-index: 999999 !important;
        }

        /* Disable built-in NProgress — we use our own custom bar */
        #nprogress {
            display: none !important;
        }

        /* Custom navigate progress bar */
        #custom-progress-bar {
            position: fixed;
            top: 156px;
            left: 12rem;
            /* sidebar w-48 */
            right: 0;
            height: 3px;
            background: #10b981;
            z-index: 999999;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease, left 0.25s ease;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
            pointer-events: none;
        }

        html.sidebar-collapsed #custom-progress-bar {
            left: 3.5rem;
        }

        @media (max-width: 1023px) {
            #custom-progress-bar {
                left: 0 !important;
            }
        }

        /* Global Livewire loading bar (component updates) */
        @keyframes loading-bar {
            0% {
                transform: translateX(-100%);
            }

            50% {
                transform: translateX(0%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-loading-bar {
            animation: loading-bar 1.2s ease-in-out infinite;
        }

        /* Skeleton pulse animation */
        @keyframes skeleton-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .skeleton {
            animation: skeleton-pulse 1.5s ease-in-out infinite;
        }

        /* Page content fade-in on navigate */
        .page-content {
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Collapsible Sidebar ===== */
        #app-sidebar {
            width: 12rem;
            transition: width 0.25s ease, transform 0.1s ease;
        }

        html.sidebar-collapsed #app-sidebar {
            width: 3.5rem;
        }

        #main-content-wrapper,
        #app-breadcrumb {
            padding-left: 12rem;
            transition: padding-left 0.25s ease;
        }

        #main-content-wrapper {
            padding-left: 12rem;
        }

        html.sidebar-collapsed #main-content-wrapper,
        html.sidebar-collapsed #app-breadcrumb {
            padding-left: 3.5rem;
        }

        /* Header bar uses left offset, not padding */

        @media (max-width: 1023px) {
            #app-sidebar {
                width: 12rem !important;
            }

            #main-content-wrapper,
            #app-breadcrumb {
                padding-left: 0 !important;
            }
        }

        /* Text labels: smooth hide on collapse */
        #app-sidebar nav a>span:last-child,
        #app-sidebar nav button>span:first-of-type,
        #app-sidebar .sidebar-section-header>span,
        #app-sidebar .sidebar-nav-item>span:last-child {
            max-width: 200px;
            overflow: hidden;
            white-space: nowrap;
            transition: max-width 0.25s ease, opacity 0.15s ease;
        }

        html.sidebar-collapsed #app-sidebar nav a>span:last-child,
        html.sidebar-collapsed #app-sidebar nav button>span:first-of-type,
        html.sidebar-collapsed #app-sidebar .sidebar-section-header>span,
        html.sidebar-collapsed #app-sidebar .sidebar-nav-item>span:last-child {
            max-width: 0;
            opacity: 0;
        }

        /* Center icons when collapsed */
        html.sidebar-collapsed #app-sidebar nav a,
        html.sidebar-collapsed #app-sidebar nav button,
        html.sidebar-collapsed #app-sidebar .sidebar-nav-item,
        html.sidebar-collapsed #app-sidebar .sidebar-section-header {
            justify-content: center;
            gap: 0;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-bottom {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Hide accordion chevron & content when collapsed */
        html.sidebar-collapsed #app-sidebar .sidebar-accordion-chevron {
            display: none;
        }

        html.sidebar-collapsed #app-sidebar .hs-accordion-content {
            display: none !important;
        }

        /* Collapse toggle button: center icon & hide label when collapsed */
        #sidebar-collapse-btn .sidebar-label {
            max-width: 100px;
            overflow: hidden;
            white-space: nowrap;
            transition: max-width 0.25s ease, opacity 0.15s ease;
        }

        html.sidebar-collapsed #sidebar-collapse-btn {
            justify-content: center;
            gap: 0;
            padding-left: 0;
            padding-right: 0;
        }

        html.sidebar-collapsed #sidebar-collapse-btn .sidebar-label {
            max-width: 0;
            opacity: 0;
        }

        /* ===== Sidebar Tooltip (JS-rendered, appended to body) ===== */
        #sidebar-tooltip {
            position: fixed;
            background: #111827;
            color: #f9fafb;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
            padding: 5px 10px;
            border-radius: 6px;
            z-index: 999999;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.12s ease;
            display: none;
        }

        #sidebar-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #111827;
        }
    </style>

    <!-- Dark Mode Initialization (must be in head to prevent flash) -->
    <script>
        // Initialize dark mode immediately before page renders
        if (localStorage.getItem('darkMode') === 'dark' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        // Initialize sidebar collapse state immediately to prevent layout shift
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
</head>

<body class="bg-gray-50 dark:bg-neutral-800">

    <!-- Custom Navigate Progress Bar -->
    <div id="custom-progress-bar"></div>

    <!-- Sidebar tooltip (position:fixed, escapes overflow clipping) -->
    <div id="sidebar-tooltip"></div>

    <x-partials.header />
    @persist('navbar')
        @livewire('partials.navbar')
    @endpersist
    <x-partials.breadcrumb />
    <x-partials.sidebar />

    <!-- Content -->
    <div id="main-content-wrapper" class="w-full pt-[156px]">
        <main class="p-4 md:p-6 page-content">
            {{ $slot }}
        </main>
    </div>
    <!-- Footer sticks to bottom -->
    <x-partials.footer />

    @livewireScripts()
    @livewireAlert()
    @stack('scripts')

    <!-- Progress Bar Controller -->
    <script>
        (function() {
            var bar = document.getElementById('custom-progress-bar');
            var rafId, progress = 0,
                active = false;

            function setWidth(p) {
                bar.style.transform = 'scaleX(' + p + ')';
            }

            function trickle() {
                if (!active) return;
                progress += Math.random() * 0.08;
                if (progress > 0.9) progress = 0.9;
                setWidth(progress);
                rafId = setTimeout(trickle, 300);
            }

            function start() {
                progress = 0.05;
                active = true;
                bar.style.opacity = '1';
                bar.style.transition = 'transform 0.3s ease';
                setWidth(progress);
                trickle();
            }

            function done() {
                clearTimeout(rafId);
                active = false;
                bar.style.transition = 'transform 0.2s ease, opacity 0.3s ease 0.15s';
                setWidth(1);
                setTimeout(function() {
                    bar.style.opacity = '0';
                    setTimeout(function() {
                        setWidth(0);
                        bar.style.opacity = '1';
                    }, 300);
                }, 200);
            }

            /* wire:navigate page transitions */
            document.addEventListener('livewire:navigating', start);
            document.addEventListener('livewire:navigated', done);

            /* Livewire component request/response (AJAX updates) */
            document.addEventListener('livewire:request', start);
            document.addEventListener('livewire:response', done);
            document.addEventListener('livewire:error', done);
        })();
    </script>

    <!-- Dark Mode Toggle Function -->
    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');

            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('darkMode', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('darkMode', 'dark');
            }

            // Reinitialize Preline components
            setTimeout(() => {
                if (window.HSStaticMethods) {
                    window.HSStaticMethods.autoInit();
                }
            }, 50);
        }

        function toggleSidebar() {
            const collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', collapsed);
            const icon = document.getElementById('sidebar-toggle-icon');
            if (icon) icon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        function initSidebarTooltips() {
            // Create tooltip element once
            var tip = document.getElementById('sidebar-tooltip');
            if (!tip) {
                tip = document.createElement('div');
                tip.id = 'sidebar-tooltip';
                document.body.appendChild(tip);
            }

            var hideTimer;

            function showTip(el, text) {
                if (!document.documentElement.classList.contains('sidebar-collapsed')) return;
                clearTimeout(hideTimer);
                var rect = el.getBoundingClientRect();
                tip.textContent = text;
                tip.style.display = 'block';
                tip.style.top = (rect.top + rect.height / 2) + 'px';
                tip.style.left = (rect.right + 10) + 'px';
                tip.style.transform = 'translateY(-50%)';
                // force reflow then fade in
                requestAnimationFrame(function() {
                    tip.style.opacity = '1';
                });
            }

            function hideTip() {
                tip.style.opacity = '0';
                hideTimer = setTimeout(function() {
                    tip.style.display = 'none';
                }, 120);
            }

            // Collect all sidebar interactive elements
            var targets = [
                ...document.querySelectorAll('#app-sidebar nav a, #app-sidebar nav button'),
                ...document.querySelectorAll('#app-sidebar .sidebar-section-header'),
                ...document.querySelectorAll('#app-sidebar .sidebar-nav-item')
            ];

            targets.forEach(function(el) {
                // Derive label if not already set
                if (!el.dataset.tooltip) {
                    var span = Array.from(el.querySelectorAll('span')).find(function(s) {
                        return s.textContent.trim().length > 0;
                    });
                    var label = (span && span.textContent.trim()) || el.title || '';
                    if (label) el.dataset.tooltip = label;
                }
                // Skip already-initialized elements to avoid duplicate listeners
                // (do NOT clone — cloning would strip Preline accordion event listeners)
                if (el.dataset.tooltipInit) return;
                if (el.dataset.tooltip) {
                    el.dataset.tooltipInit = '1';
                    el.addEventListener('mouseenter', function() {
                        showTip(el, el.dataset.tooltip);
                    });
                    el.addEventListener('mouseleave', hideTip);
                }
            });
        }

        function restoreThemeState() {
            const html = document.documentElement;
            // Restore dark mode
            if (localStorage.getItem('darkMode') === 'dark' ||
                (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
            // Restore sidebar collapsed state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                html.classList.add('sidebar-collapsed');
            } else {
                html.classList.remove('sidebar-collapsed');
            }
            // Sync sidebar toggle icon
            const icon = document.getElementById('sidebar-toggle-icon');
            if (icon) {
                icon.style.transform = html.classList.contains('sidebar-collapsed') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const icon = document.getElementById('sidebar-toggle-icon');
            if (icon && document.documentElement.classList.contains('sidebar-collapsed')) {
                icon.style.transform = 'rotate(180deg)';
            }
            initSidebarTooltips();
        });
        document.addEventListener('livewire:navigated', function() {
            restoreThemeState();
            initSidebarTooltips();
        });
    </script>
</body>

</html>
