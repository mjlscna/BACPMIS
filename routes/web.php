<?php
use App\Livewire\AdminPanel\UserListPage;
use App\Livewire\ModeOfProcurementPage;
use App\Livewire\PostPage;
use App\Livewire\Procurement\CreatePage;
use App\Livewire\Procurement\EditPage;
use App\Livewire\Procurement\IndexPage;
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

    Route::prefix('procurements')->name('procurements.')->group(function () {
        Route::get('/', IndexPage::class)->name('index');
        Route::get('/create', CreatePage::class)->name('create');
        Route::get('/{procurement}/edit', EditPage::class)->name('edit');
    });

    Route::get('/modeofprocurements', ModeOfProcurementPage::class)->name('modeofprocurements.index');
    Route::get('/posts', PostPage::class)->name('posts.index');

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




