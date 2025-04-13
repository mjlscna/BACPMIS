<?php


use App\Livewire\HomePage;
use App\Livewire\ProcurementPage;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

// Set '/' to load your Livewire HomePage
Route::get('/user', HomePage::class);

Route::get('/', function () {
    return redirect(Filament::getHomeUrl());
});
Route::get('/dashboard', ProcurementPage::class)->name('dashboard.page');
Route::get('/procurement', ProcurementPage::class)->name('procurement.page');


