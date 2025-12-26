<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminBackendAppearance\Http\Controllers\AdminBackendAppearanceController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin/settings/appearance"], function () {
        Route::resource('/', AdminBackendAppearanceController::class)->only(['index'])->names('admin.appearance');
        Route::post('save', [ AdminBackendAppearanceController::class, 'save' ])->name('admin.appearance.save');
    });
});

