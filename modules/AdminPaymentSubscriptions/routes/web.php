<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPaymentSubscriptions\Http\Controllers\AdminPaymentSubscriptionsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "payment/subscriptions"], function () {
            Route::resource('/', AdminPaymentSubscriptionsController::class)->only(['index'])->names('admin.payment.subscriptions');
            Route::post('list', [AdminPaymentSubscriptionsController::class, 'list'])->name('admin.payment.subscriptions.list');
            Route::post('update', [AdminPaymentSubscriptionsController::class, 'update'])->name('admin.payment.subscriptions.update');
            Route::post('save', [AdminPaymentSubscriptionsController::class, 'save'])->name('admin.payment.subscriptions.save');
            Route::post('destroy', [AdminPaymentSubscriptionsController::class, 'destroy'])->name('admin.payment.subscriptions.destroy');
            Route::post('status/{any}', [AdminPaymentSubscriptionsController::class, 'status'])->name('admin.payment.subscriptions.status');
        });
    });
});