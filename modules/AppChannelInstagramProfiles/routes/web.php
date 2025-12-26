<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelInstagramProfiles\Http\Controllers\AppChannelInstagramProfilesController;

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
        Route::group(["prefix" => "instagram"], function () {
            Route::group(["prefix" => "profile"], function () {
                Route::resource('/', AppChannelInstagramProfilesController::class)->names('app.channelinstagramprofiles');
                Route::get('oauth', [AppChannelInstagramProfilesController::class, 'oauth'])->name('app.channelinstagramprofiles.oauth');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('instagram', [AppChannelInstagramProfilesController::class, 'settings'])->name('app.channelinstagramprofiles.settings');
    });
});