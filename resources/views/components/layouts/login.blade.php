<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/DOH_Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-slate-100 dark:bg-slate-700">
    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
