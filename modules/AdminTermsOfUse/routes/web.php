<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminTermsOfUse\Http\Controllers\AdminTermsOfUseController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "frontend/terms-of-use"], function () {
            Route::get('/', [AdminTermsOfUseController::class, 'settings'])->name('admin.terms-of-use.settings');
        });
    });
});
