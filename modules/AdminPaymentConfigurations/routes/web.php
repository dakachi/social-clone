<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPaymentConfigurations\Http\Controllers\AdminPaymentConfigurationsController;

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
    Route::group(["prefix" => "admin/payment/configuration"], function () {
        Route::resource('/', AdminPaymentConfigurationsController::class)->only(['index'])->names('admin.payment.configuration');
        Route::post('list', [AdminPaymentConfigurationsController::class, 'list'])->name('admin.payment.configuration.list');
    });
});