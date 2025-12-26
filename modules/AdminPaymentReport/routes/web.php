<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminPaymentReport\Http\Controllers\AdminPaymentReportController;

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
        Route::group(["prefix" => "payment/report"], function () {
            Route::get('/', [AdminPaymentReportController::class, 'index'])->name('admin.payment.report');
            Route::post('statistics', [AdminPaymentReportController::class, 'statistics'])->name('admin.payment.report.statistics');
            Route::post('export_pdf', [AdminPaymentReportController::class, 'exportPdf'])->name('admin.payment.report.export_pdf');
        });
    });
});