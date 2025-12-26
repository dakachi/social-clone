<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelFacebookProfiles\Http\Controllers\AppChannelFacebookProfilesController;

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
    Route::group(["prefix" => "facebook"], function () {
        Route::group(["prefix" => "profile"], function () {
            Route::resource('/', AppChannelFacebookProfilesController::class)->names('app.channelfacebookprofiles');
            Route::get('oauth', [AppChannelFacebookProfilesController::class, 'oauth'])->name('app.channelfacebookprofiles.oauth');
        });
    });
});