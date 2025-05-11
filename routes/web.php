<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SearchBabysitterController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware('auth')->group(
    function () {
        Route::get('/search', SearchBabysitterController::class)
            ->name('search.babysitter');
        Route::get('/reservations', [ReservationController::class, 'index'])
            ->name('reservations.index');
    }
);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
