<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelYoutubeChannels\Http\Controllers\AppChannelYoutubeChannelsController;

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
        Route::group(["prefix" => "youtube"], function () {
            Route::group(["prefix" => "channel"], function () {
                Route::resource('/', AppChannelYoutubeChannelsController::class)->names('app.channelyoutubechannels');
                Route::get('oauth', [AppChannelYoutubeChannelsController::class, 'oauth'])->name('app.channelyoutubechannels.oauth');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('youtube', [AppChannelYoutubeChannelsController::class, 'settings'])->name('app.channelyoutubechannels.settings');
    });
});