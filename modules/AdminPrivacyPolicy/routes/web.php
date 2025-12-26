<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPrivacyPolicy\Http\Controllers\AdminPrivacyPolicyController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "frontend/privacy-policy"], function () {
            Route::get('/', [AdminPrivacyPolicyController::class, 'settings'])->name('admin.privacy-policy.settings');
        });
    });
});
