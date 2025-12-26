<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminUserReport\Http\Controllers\AdminUserReportController;

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
        Route::group(["prefix" => "users/report"], function () {
            Route::get('/', [AdminUserReportController::class, 'index'])->name('admin.users.report');
            Route::post('statistics', [AdminUserReportController::class, 'statistics'])->name('admin.users.report.statistics');
            Route::post('export_pdf', [AdminUserReportController::class, 'exportPdf'])->name('admin.users.report.export_pdf');
        });
    });
});