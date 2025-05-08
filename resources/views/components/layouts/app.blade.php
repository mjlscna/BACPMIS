<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'BACPMS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles()
</head>

<body>
    @livewire('partials.header')
    @livewire('partials.navbar')
    @livewire('partials.sidebar')
    <!-- Content -->
    <div class="w-full lg:ps-60 pt-45">

        <main>
            {{ $slot }}
        </main>
    </div>
    @livewire('partials.footer')

    @livewireScripts()
    @livewireAlert() <!-- Correct placement for LivewireAlert -->
</body>

</html>
