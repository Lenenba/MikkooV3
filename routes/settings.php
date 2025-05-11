<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\MediaController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\BabysitterProfileController;
use App\Http\Controllers\Settings\SetAsProfilePhotoController;
use App\Http\Controllers\Settings\DeleteProfilePhotoController;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

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

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');
});
