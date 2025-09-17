<?php

use App\Livewire\ModeOfProcurement\ModeOfProcurementCreatePage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementEditPage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementIndexPage;
use App\Livewire\Procurements\ProcurementCreatePage;
use App\Livewire\Procurements\ProcurementEditPage;
use App\Livewire\Procurements\ProcurementIndexPage;
use Illuminate\Support\Facades\Route;
use App\Livewire\LoginPage;
use App\Livewire\HomePage;

// Public login route
Route::get('/login', LoginPage::class)
    ->middleware('guest')
    ->name('login');

// Protected routes with JwtMiddleware and Filament Shield
Route::middleware(['jwt'])->group(function () {

    Route::get('/', HomePage::class)->name('dashboard');

    // Procurement routes with Shield permissions
    Route::prefix('procurements')->name('procurements.')->group(function () {
        Route::get('/', ProcurementIndexPage::class)
            ->name('index')
            ->middleware('can:view_any_procurement');

        Route::get('/create', ProcurementCreatePage::class)
            ->name('create')
            ->middleware('can:create_procurement');

        Route::get('/{procurement}/edit', ProcurementEditPage::class)
            ->name('edit')
            ->middleware('can:update_procurement');
    });

    // Mode of procurement routes with Shield permissions
    Route::prefix('mode-of-procurement')->name('mode-of-procurement.')->group(function () {
        Route::get('/', ModeOfProcurementIndexPage::class)
            ->name('index')
            ->middleware('can:view_any_mode::of::procurement');

        Route::get('/create', ModeOfProcurementCreatePage::class)
            ->name('create')
            ->middleware('can:create_mode::of::procurement');

        Route::get('/{id}/edit', ModeOfProcurementEditPage::class)
            ->name('edit')
            ->middleware('can:edit_mode::of::procurement');
    });

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        session()->forget(['jwt_token', 'roleName', 'user']);
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
