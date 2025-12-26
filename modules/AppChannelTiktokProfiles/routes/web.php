<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelTiktokProfiles\Http\Controllers\AppChannelTiktokProfilesController;

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
        Route::group(["prefix" => "tiktok"], function () {
            Route::group(["prefix" => "profile"], function () {
                Route::resource('/', AppChannelTiktokProfilesController::class)->names('app.channeltiktokprofiles');
                Route::get('oauth', [AppChannelTiktokProfilesController::class, 'oauth'])->name('app.channeltiktokprofiles.oauth');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('tiktok', [AppChannelTiktokProfilesController::class, 'settings'])->name('app.channeltiktokprofiles.settings');
    });
});