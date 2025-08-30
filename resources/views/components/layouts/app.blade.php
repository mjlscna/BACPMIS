<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'BACPMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles()
</head>

<body class="flex flex-col min-h-screen">

    @livewire('partials.header')
    @livewire('partials.navbar')
    @livewire('partials.sidebar')

    <!-- Content -->
    <div class="w-full pt-45 flex-grow lg:max-w-[calc(100vw-14rem)] lg:ml-[14rem]">
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Footer sticks to bottom -->
    @livewire('partials.footer')

    @livewireScripts()
    @livewireAlert()
</body>


</html>
