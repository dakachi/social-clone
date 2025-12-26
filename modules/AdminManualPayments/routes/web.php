<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminManualPayments\Http\Controllers\AdminManualPaymentsController;

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

Route::group(["prefix" => "admin"], function () {
    Route::resource('adminmanualpayments', AdminManualPaymentsController::class)->names('admin.manualpayments');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "manual-payments"], function () {
            Route::resource('/', AdminManualPaymentsController::class)->only(['index'])->names('admin.manualpayments');
            Route::post('list', [AdminManualPaymentsController::class, 'list'])->name('admin.manualpayments.list');
            Route::post('update', [AdminManualPaymentsController::class, 'update'])->name('admin.manualpayments.update');
            Route::post('save', [AdminManualPaymentsController::class, 'save'])->name('admin.manualpayments.save');
            Route::post('destroy', [AdminManualPaymentsController::class, 'destroy'])->name('admin.manualpayments.destroy');
            Route::post('status/{any}', [AdminManualPaymentsController::class, 'status'])->name('admin.manualpayments.status');
        });
    });
});
