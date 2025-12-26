<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPaymentHistory\Http\Controllers\AdminPaymentHistoryController;

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
        Route::group(["prefix" => "payment/history"], function () {
            Route::resource('/', AdminPaymentHistoryController::class)->only(['index'])->names('admin.payment.history');
            Route::post('list', [AdminPaymentHistoryController::class, 'list'])->name('admin.payment.history.list');
            Route::post('update', [AdminPaymentHistoryController::class, 'update'])->name('admin.payment.history.edit');
            Route::post('save', [AdminPaymentHistoryController::class, 'save'])->name('admin.payment.history.save');
            Route::post('destroy', [AdminPaymentHistoryController::class, 'destroy'])->name('admin.payment.history.destroy');
            Route::post('status/{any}', [AdminPaymentHistoryController::class, 'status'])->name('admin.payment.history.status');
        });
    });
});
