<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAnalytics\Http\Controllers\AppAnalyticsController;

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
    Route::group(["prefix" => "app"], function () {
        Route::group(["prefix" => "analytics"], function () {
            Route::resource('/', AppAnalyticsController::class)->names('app.analytics');
            Route::get('{social}/{id_secure}', [AppAnalyticsController::class, 'show'])->name('app.analytics.show');
            Route::post('{social}/{id_secure}/export-pdf', [AppAnalyticsController::class, 'exportPdf'])->name('analytics.export.pdf');
        });
    });
});