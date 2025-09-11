<?php

use App\Livewire\ModeOfProcurement\ModeOfProcurementCreatePage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementEditPage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementIndexPage;
use App\Livewire\Procurements\ProcurementCreatePage;
use App\Livewire\Procurements\ProcurementEditPage;
use App\Livewire\Procurements\ProcurementIndexPage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\LoginPage;
use App\Livewire\HomePage;



// ✅ Public login route (unauthenticated)
Route::get('/login', LoginPage::class)
    ->middleware('guest')
    ->name('login');

// ✅ Protected routes (only for authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/', HomePage::class)->name('dashboard.page');

    Route::prefix('procurements')->name('procurements.')->group(function () {
        Route::get('/', ProcurementIndexPage::class)->name('index');
        Route::get('/create', ProcurementCreatePage::class)->name('create');
        Route::get('/{procurement}/edit', ProcurementEditPage::class)->name('edit');
    });

    Route::prefix('mode-of-procurement')->name('mode-of-procurement.')->group(function () {
        Route::get('/', ModeOfProcurementIndexPage::class)->name('index');
        Route::get('/create', ModeOfProcurementCreatePage::class)->name('create');
        Route::get('/{id}/edit', ModeOfProcurementEditPage::class)->name('edit');
    });

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




