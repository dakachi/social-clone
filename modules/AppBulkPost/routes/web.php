<?php

use Illuminate\Support\Facades\Route;
use Modules\AppBulkPost\Http\Controllers\AppBulkPostController;

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
        Route::group(["prefix" => "bulk-post"], function () {
            Route::resource('/', AppBulkPostController::class)->only(['index'])->names('app.bulk-post');
            Route::post('save', [AppBulkPostController::class, 'save'])->name('app.bulk-post.save');
            Route::get('download-template', [AppBulkPostController::class, 'download_template'])->name('app.bulk-post.download-template');
        });
    });
});