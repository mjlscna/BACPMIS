<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'WV CHD PMIS' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/DOH_Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles()

</head>

<body class="bg-gray-50 dark:bg-neutral-900">

    @livewire('partials.header')
    @livewire('partials.navbar')
    @livewire('partials.sidebar')

    <!-- Content -->
    <div class="w-full lg:pl-55 pt-[156px]">
        <main class="p-4 md:p-6">
            {{ $slot }}
        </main>
    </div>
    <!-- Footer sticks to bottom -->
    @livewire('partials.footer')

    @livewireScripts()
    @livewireAlert()
</body>


</html>
