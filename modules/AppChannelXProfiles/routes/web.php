<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelXProfiles\Http\Controllers\AppChannelXProfilesController;

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
        Route::group(["prefix" => "x"], function () {
            Route::group(["prefix" => "profile"], function () {
                Route::resource('/', AppChannelXProfilesController::class)->names('app.channelxprofiles');
                Route::get('oauth', [AppChannelXProfilesController::class, 'oauth'])->name('app.channelxprofiles.oauth');
            });
        });
    }); 

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('x', [AppChannelXProfilesController::class, 'settings'])->name('app.channelxprofiles.settings');
    });
});