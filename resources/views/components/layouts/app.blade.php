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
    <div class="w-full lg:ps-60">
        <div class="">
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewire('partials.footer')
    </div>
    @livewireScripts()
    <!-- Flash Message Script -->
    <script>
        // Check if there's a flash message and fade it out after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessage = document.querySelector('.flash-message');
            if (flashMessage) {
                setTimeout(function() {
                    flashMessage.classList.add('opacity-0'); // Fade out the message
                    // Optional: Remove the message after it fades out
                    setTimeout(function() {
                        flashMessage.remove();
                    }, 300); // Wait for fade-out animation to complete
                }, 3000); // Delay of 3 seconds before hiding
            }
        });
    </script>
</body>

</html>
