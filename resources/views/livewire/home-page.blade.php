<div>
    <h1 class="text-4xl text-red-500">Dashboard</h1>

    @auth
        <p class="text-green-500">✅ Logged in as {{ auth()->user()->email }}</p>
    @else
        <p class="text-yellow-500">❌ Not logged in</p>
    @endauth
</div>
