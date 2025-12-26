<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelXUnofficial\Http\Controllers\AppChannelXUnofficialController;

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
        Route::group(["prefix" => "x_unofficial"], function () {
            Route::group(["prefix" => "profile"], function () {
                Route::resource('/', AppChannelXUnofficialController::class)->names('app.channelxunofficial');
                Route::get('oauth', [AppChannelXUnofficialController::class, 'oauth'])->name('app.channelxunofficial.oauth');
                Route::post('proccess', [AppChannelXUnofficialController::class, 'proccess'])->name('app.channelxunofficial.proccess');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('x', [AppChannelXUnofficialController::class, 'settings'])->name('app.channelxunofficial.settings');
    });
});