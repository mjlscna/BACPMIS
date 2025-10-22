<?php

use App\Livewire\BacApprovedPr\BacApprovedPrCreatePage;
use App\Livewire\BacApprovedPr\BacApprovedPrEditPage;
use App\Livewire\BacApprovedPr\BacApprovedPrIndexPage;
use App\Livewire\BacApprovedPr\BacApprovedPrViewPage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementCreatePage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementEditPage;
use App\Livewire\ModeOfProcurement\ModeOfProcurementIndexPage;
use App\Livewire\Procurements\ProcurementCreatePage;
use App\Livewire\Procurements\ProcurementEditPage;
use App\Livewire\Procurements\ProcurementIndexPage;
use App\Livewire\ScheduleForPr\ScheduleForPrCreatePage;
use App\Livewire\ScheduleForPr\ScheduleForPrEditPage;
use App\Livewire\ScheduleForPr\ScheduleForPrIndexPage;
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
            ->middleware('can:edit_procurement');
    });

    Route::prefix('bac-approved-pr')->name('bac-approved-pr.')->group(function () {
        Route::get('/', BacApprovedPrIndexPage::class)
            ->name('index')
            ->middleware('can:view_any_b::a::c::approved::p::r');

        Route::get('/create', BacApprovedPrCreatePage::class)
            ->name('create')
            ->middleware('can:create_b::a::c::approved::p::r');

        Route::get('/{bacapprovedpr}/edit', BacApprovedPrEditPage::class)
            ->name('edit')
            ->middleware('can:edit_b::a::c::approved::p::r');

        Route::get('/{bacapprovedpr}', BacApprovedPrViewPage::class)
            ->name('view')
            ->middleware('can:view_b::a::c::approved::p::r');
    });

    Route::prefix('schedule-for-procurement')->name('schedule-for-procurement.')->group(function () {
        Route::get('/', ScheduleForPrIndexPage::class)
            ->name('index')
            ->middleware('can:view_any_schedule::for::procurement');

        Route::get('/create', ScheduleForPrCreatePage::class)
            ->name('create')
            ->middleware('can:create_schedule::for::procurement');

        Route::get('/{id}/edit', ScheduleForPrEditPage::class)
            ->name('edit')
            ->middleware('can:edit_schedule::for::procurement');
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
            ->middleware('can:edi_mode::of::procurement');


    });

    // Logout
    Route::post('/logout', function () {
        session()->forget(['jwt_token', 'roleName', 'user']);
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
