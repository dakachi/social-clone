<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelLinkedinPages\Http\Controllers\AppChannelLinkedinPagesController;

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
    Route::group(["prefix" => "linkedin"], function () {
        Route::group(["prefix" => "page"], function () {
            Route::resource('/', AppChannelLinkedinPagesController::class)->names('app.channellinkedinpages');
            Route::get('oauth', [AppChannelLinkedinPagesController::class, 'oauth'])->name('app.channellinkedinpages.oauth');
        });
    });
});