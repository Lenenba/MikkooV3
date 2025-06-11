<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServicesSearchController;
use App\Http\Controllers\SearchBabysitterController;
use App\Http\Controllers\AcceptReservationController;
use App\Http\Controllers\CancelReservationController;

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

        Route::post('/reservations/{reservationId}/accept', AcceptReservationController::class)
            ->name('reservations.accept');
        Route::post('/reservations/{reservationId}/cancel', CancelReservationController::class)
            ->name('reservations.cancel');
        Route::get('/reservations/{id}/create', [ReservationController::class, 'create'])
            ->name('reservations.create');
        Route::get('/service/search', ServicesSearchController::class)
            ->name('service.search');
    }
);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
