<?php

use App\Http\Controllers\Admin\Workshops\WorkshopIndexController as AdminWorkshopIndexController;
use App\Http\Controllers\App\Workshops\WorkshopIndexController as AppWorkshopIndexController;
use App\Models\Workshop;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::middleware(['can:viewAny,'.Workshop::class])
        ->prefix('app')
        ->as('app.')
        ->group(function () {
            Route::get('workshops', AppWorkshopIndexController::class)->name('workshops.index');
        });

    Route::middleware(['can:create,'.Workshop::class])
        ->prefix('admin')
        ->as('admin.')
        ->group(function () {
            Route::get('workshops', AdminWorkshopIndexController::class)->name('workshops.index');
        });
});

require __DIR__.'/settings.php';
