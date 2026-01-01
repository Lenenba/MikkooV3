<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\MediaController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ParentProfileController;
use App\Http\Controllers\Settings\BabysitterProfileController;
use App\Http\Controllers\Settings\BabysitterServicesController;
use App\Http\Controllers\Settings\SetAsProfilePhotoController;
use App\Http\Controllers\Settings\DeleteProfilePhotoController;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    // Babysitter services
    Route::get('settings/services', [BabysitterServicesController::class, 'index'])->name('services.index');
    Route::post('settings/services', [BabysitterServicesController::class, 'store'])->name('services.store');
    Route::patch('settings/services/{service}', [BabysitterServicesController::class, 'update'])->name('services.update');
    Route::delete('settings/services/{service}', [BabysitterServicesController::class, 'destroy'])->name('services.destroy');

    // Media management
    Route::get('settings/media', [MediaController::class, 'index'])->name('media.list');
    Route::post('settings/media', [MediaController::class, 'store'])->name('media.store');
    Route::post('settings/media/setAsProfile', SetAsProfilePhotoController::class)->name('media.setProfile');
    Route::delete('settings/media/{media}', DeleteProfilePhotoController::class)->name('media.delete');

    // Babysitter profile details
    Route::get('settings/babysitter/profile/details', [BabysitterProfileController::class, 'edit'])
        ->name('babysitter.profile.details');
    Route::patch('settings/babysitter/profile/details', [BabysitterProfileController::class, 'update'])
        ->name('babysitter.profile.update');

    // Parent profile details
    Route::get('settings/parent/profile/details', [ParentProfileController::class, 'edit'])
        ->name('parent.profile.details');
    Route::patch('settings/parent/profile/details', [ParentProfileController::class, 'update'])
        ->name('parent.profile.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');
});
