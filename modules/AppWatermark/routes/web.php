<?php

use Illuminate\Support\Facades\Route;
use Modules\AppWatermark\Http\Controllers\AppWatermarkController;

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

Route::group(["prefix" => "app"], function () {
    Route::group(["prefix" => "watermark"], function () {
        Route::resource('/', AppWatermarkController::class)->only(['index'])->names('app.watermark');
        Route::post('load', [AppWatermarkController::class, 'load'])->name('app.watermark.load');
        Route::post('update', [AppWatermarkController::class, 'update'])->name('app.watermark.update');
        Route::post('save', [AppWatermarkController::class, 'save'])->name('app.watermark.save');
        Route::post('destroy', [AppWatermarkController::class, 'destroy'])->name('app.watermark.destroy');
        Route::post('status/{any}', [AppWatermarkController::class, 'status'])->name('app.watermark.status');
    });
});