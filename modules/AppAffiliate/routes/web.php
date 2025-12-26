<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAffiliate\Http\Controllers\AppAffiliateController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "app/affiliate"], function () {
        Route::resource('/', AppAffiliateController::class)->only(['index'])->names('app.affiliate');
        Route::post('status/{any}', [AppAffiliateController::class, 'status'])->name('app.affiliate.status');
        Route::post('update', [ AppAffiliateController::class, 'update' ])->name('app.affiliate.update');
        Route::post('list', [ AppAffiliateController::class, 'list' ])->name('app.support.list');
        Route::post('withdrawal-request', [AppAffiliateController::class, 'withdrawalRequest'])->name('app.support.withdrawal_request');
        Route::post('/send-affiliate', [AppAffiliateController::class, 'send'])->name('app.send-affiliate');
    });
});

