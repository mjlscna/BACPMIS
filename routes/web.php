<?php
use App\Livewire\AdminPanel\UserListPage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\LoginPage;
use App\Livewire\HomePage;
use App\Livewire\ProcurementPage;

// ✅ Public login route (unauthenticated)
Route::get('/login', LoginPage::class)
    ->middleware('guest')
    ->name('login');

// ✅ Protected routes (only for authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/', HomePage::class)->name('dashboard.page');
    Route::get('/procurement', ProcurementPage::class)->name('procurement.page');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

Route::get('/test-auth', function () {
    return auth()->check() ? '✅ Logged in as ' . auth()->user()->email : '❌ Not logged in';
});




