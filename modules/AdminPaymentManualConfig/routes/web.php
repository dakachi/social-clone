<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPaymentManualConfig\Http\Controllers\AdminPaymentManualConfigController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin/payment/manual-config"], function () {
        Route::resource('/', AdminPaymentManualConfigController::class)->only(['index'])->names('admin.payment.manual-config');
        Route::post('list', [AdminPaymentManualConfigController::class, 'list'])->name('admin.payment.manual-config.list');
    });
});
