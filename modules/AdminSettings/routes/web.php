<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSettings\Http\Controllers\AdminSettingsController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin/settings"], function () {
        Route::resource('/', AdminSettingsController::class)->only(['index'])->names('admin.settings');
        Route::get('pusher', [ AdminSettingsController::class, 'pusher' ])->name('admin.settings.pusher');
        Route::post('save', [ AdminSettingsController::class, 'save' ])->name('admin.settings.save');
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('pusher', [AdminSettingsController::class, 'pusher'])->name('admin.pusher.settings');
    });
});
