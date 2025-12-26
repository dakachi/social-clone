<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelInstagramUnofficial\Http\Controllers\AppChannelInstagramUnofficialController;

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
        Route::group(["prefix" => "instagram_unofficial"], function () {
            Route::group(["prefix" => "profile"], function () {
                Route::resource('/', AppChannelInstagramUnofficialController::class)->names('app.channelinstagramunofficial');
                Route::get('oauth', [AppChannelInstagramUnofficialController::class, 'oauth'])->name('app.channelinstagramunofficial.oauth');
                Route::post('proccess', [AppChannelInstagramUnofficialController::class, 'proccess'])->name('app.channelinstagramunofficial.proccess');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('instagram', [AppChannelInstagramUnofficialController::class, 'settings'])->name('app.channelinstagramunofficial.settings');
    });
});