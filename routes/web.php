<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReservationRatingController;
use App\Http\Controllers\ServicesSearchController;
use App\Http\Controllers\SearchBabysitterController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementApplicationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SuperAdmin\ConsultationsController;
use App\Http\Controllers\AddressOnboardingController;
use App\Http\Controllers\AcceptReservationController;
use App\Http\Controllers\CancelReservationController;
use App\Http\Controllers\CompleteReservationController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserHasAddress;

Route::get('/', HomeController::class)->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', EnsureUserHasAddress::class])
    ->name('dashboard');

Route::middleware(['auth', EnsureSuperAdmin::class])->group(function () {
    Route::get('/superadmin/consultations', ConsultationsController::class)
        ->name('superadmin.consultations');
});

Route::get('/onboarding', [OnboardingController::class, 'index'])
    ->name('onboarding.index');

Route::post('/onboarding/profile', [OnboardingController::class, 'storeProfile'])
    ->middleware('auth')
    ->name('onboarding.profile.store');

Route::post('/onboarding/availability', [OnboardingController::class, 'storeAvailability'])
    ->middleware('auth')
    ->name('onboarding.availability.store');

Route::post('/onboarding/finish', [OnboardingController::class, 'finish'])
    ->middleware('auth')
    ->name('onboarding.finish');



Route::middleware(['auth', EnsureUserHasAddress::class])->group(
    function () {
        Route::get('/onboarding/address', [AddressOnboardingController::class, 'show'])
            ->name('onboarding.address');
        Route::get('/onboarding/address/search', [AddressOnboardingController::class, 'search'])
            ->middleware('throttle:30,1')
            ->name('onboarding.address.search');
        Route::post('/onboarding/address', [AddressOnboardingController::class, 'store'])
            ->name('onboarding.address.store');

        Route::get('/search', SearchBabysitterController::class)
            ->name('search.babysitter');
        Route::get('/reservations', [ReservationController::class, 'index'])
            ->name('reservations.index');
        Route::post('/reservations', [ReservationController::class, 'store'])
            ->name('reservations.store');
        Route::get('/reservations/{id}/show', [ReservationController::class, 'show'])
            ->name('reservations.show');
        Route::post('/reservations/{reservation}/ratings', [ReservationRatingController::class, 'store'])
            ->name('reservations.ratings.store');

        Route::post('/reservations/{reservationId}/accept', AcceptReservationController::class)
            ->name('reservations.accept');
        Route::post('/reservations/{reservationId}/cancel', CancelReservationController::class)
            ->name('reservations.cancel');
        Route::post('/reservations/{reservationId}/complete', CompleteReservationController::class)
            ->name('reservations.complete');
        Route::get('/reservations/{id}/create', [ReservationController::class, 'create'])
            ->name('reservations.create');
        Route::get('/service/search', ServicesSearchController::class)
            ->name('service.search');
        Route::get('/invoices', [InvoiceController::class, 'index'])
            ->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
            ->name('invoices.show');
        Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update'])
            ->name('invoices.update');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
            ->name('invoices.download');

        Route::get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])
            ->name('announcements.show');
        Route::post('/announcements', [AnnouncementController::class, 'store'])
            ->name('announcements.store');
        Route::patch('/announcements/{announcement}', [AnnouncementController::class, 'update'])
            ->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');
        Route::post('/announcements/{announcement}/apply', [AnnouncementApplicationController::class, 'store'])
            ->name('announcements.apply');
        Route::post('/announcements/{announcement}/applications/{application}/accept', [AnnouncementApplicationController::class, 'accept'])
            ->name('announcements.applications.accept');
        Route::post('/announcements/{announcement}/applications/{application}/reject', [AnnouncementApplicationController::class, 'reject'])
            ->name('announcements.applications.reject');
        Route::post('/announcements/{announcement}/applications/{application}/withdraw', [AnnouncementApplicationController::class, 'withdraw'])
            ->name('announcements.applications.withdraw');
    }
);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
