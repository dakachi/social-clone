<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelLinkedinProfiles\Http\Controllers\AppChannelLinkedinProfilesController;

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
        Route::group(["prefix" => "linkedin"], function () {
            Route::group(["prefix" => "profile"], function () {
                Route::resource('/', AppChannelLinkedinProfilesController::class)->names('app.channellinkedinprofiles');
                Route::get('oauth', [AppChannelLinkedinProfilesController::class, 'oauth'])->name('app.channellinkedinprofiles.oauth');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('linkedin', [AppChannelLinkedinProfilesController::class, 'settings'])->name('app.channellinkedinprofiles.settings');
    });
});