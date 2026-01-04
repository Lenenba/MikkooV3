<?php

use App\Http\Controllers\Api\AnnouncementApplicationController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\BabysitterServiceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OnboardingAddressController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\ReservationActionController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReservationMediaController;
use App\Http\Controllers\Api\ReservationRatingController;
use App\Http\Controllers\Api\SearchBabysitterController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\Api\Settings\BabysitterProfileController as SettingsBabysitterProfileController;
use App\Http\Controllers\Api\Settings\DeleteMediaController as SettingsDeleteMediaController;
use App\Http\Controllers\Api\Settings\MediaController as SettingsMediaController;
use App\Http\Controllers\Api\Settings\ParentProfileController as SettingsParentProfileController;
use App\Http\Controllers\Api\Settings\PasswordController as SettingsPasswordController;
use App\Http\Controllers\Api\Settings\ProfileController as SettingsProfileController;
use App\Http\Controllers\Api\Settings\ServicesController as SettingsServicesController;
use App\Http\Controllers\Api\Settings\SetProfilePhotoController as SettingsSetProfilePhotoController;
use App\Http\Controllers\ServicesSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', RegisterController::class);
    Route::post('auth/login', LoginController::class);
    Route::post('auth/forgot-password', ForgotPasswordController::class);
    Route::post('auth/reset-password', ResetPasswordController::class);
    Route::get('auth/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.verification.verify');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', LogoutController::class);
        Route::get('me', MeController::class);
        Route::post('auth/email/verification-notification', EmailVerificationNotificationController::class)
            ->middleware('throttle:6,1');

        Route::get('onboarding', [OnboardingController::class, 'index']);
        Route::post('onboarding/profile', [OnboardingController::class, 'storeProfile']);
        Route::get('onboarding/address/search', [OnboardingAddressController::class, 'search'])
            ->middleware('throttle:30,1');
        Route::post('onboarding/address', [OnboardingAddressController::class, 'store']);
        Route::post('onboarding/availability', [OnboardingController::class, 'storeAvailability']);
        Route::post('onboarding/finish', [OnboardingController::class, 'finish']);

        Route::get('search/babysitters', SearchBabysitterController::class);
        Route::get('services/search', ServicesSearchController::class);
        Route::get('babysitters/{babysitter}/services', BabysitterServiceController::class);

        Route::get('reservations', [ReservationController::class, 'index']);
        Route::get('reservations/{id}', [ReservationController::class, 'show']);
        Route::post('reservations', [ReservationController::class, 'store']);
        Route::post('reservations/{reservationId}/accept', [ReservationActionController::class, 'accept']);
        Route::post('reservations/{reservationId}/cancel', [ReservationActionController::class, 'cancel']);
        Route::post('reservations/{reservationId}/start', [ReservationActionController::class, 'start']);
        Route::post('reservations/{reservationId}/complete', [ReservationActionController::class, 'complete']);
        Route::post('reservations/{reservation}/ratings', [ReservationRatingController::class, 'store']);
        Route::get('reservations/{reservation}/media', [ReservationMediaController::class, 'index']);
        Route::post('reservations/{reservation}/media', [ReservationMediaController::class, 'store']);

        Route::post('push-tokens', [PushTokenController::class, 'store']);
        Route::delete('push-tokens', [PushTokenController::class, 'destroy']);

        Route::get('announcements', [AnnouncementController::class, 'index']);
        Route::post('announcements', [AnnouncementController::class, 'store']);
        Route::get('announcements/{announcement}', [AnnouncementController::class, 'show']);
        Route::patch('announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::post('announcements/{announcement}/apply', [AnnouncementApplicationController::class, 'store']);
        Route::post('announcements/{announcement}/applications/{application}/accept', [AnnouncementApplicationController::class, 'accept']);
        Route::post('announcements/{announcement}/applications/{application}/reject', [AnnouncementApplicationController::class, 'reject']);
        Route::post('announcements/{announcement}/applications/{application}/withdraw', [AnnouncementApplicationController::class, 'withdraw']);

        Route::get('settings/profile', [SettingsProfileController::class, 'show']);
        Route::patch('settings/profile', [SettingsProfileController::class, 'update']);
        Route::put('settings/password', [SettingsPasswordController::class, 'update']);
        Route::patch('settings/parent/profile', [SettingsParentProfileController::class, 'update']);
        Route::patch('settings/babysitter/profile', [SettingsBabysitterProfileController::class, 'update']);

        Route::get('settings/services', [SettingsServicesController::class, 'index']);
        Route::post('settings/services', [SettingsServicesController::class, 'store']);
        Route::patch('settings/services/{service}', [SettingsServicesController::class, 'update']);
        Route::delete('settings/services/{service}', [SettingsServicesController::class, 'destroy']);

        Route::get('settings/media', [SettingsMediaController::class, 'index']);
        Route::post('settings/media', [SettingsMediaController::class, 'store']);
        Route::post('settings/media/set-profile', SettingsSetProfilePhotoController::class);
        Route::delete('settings/media/{mediaId}', SettingsDeleteMediaController::class);

        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::patch('invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download']);

        Route::get('dashboard', DashboardController::class);
    });
});
