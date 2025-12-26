<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannelFacebookPages\Http\Controllers\AppChannelFacebookPagesController;

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
        Route::group(["prefix" => "facebook"], function () {
            Route::group(["prefix" => "page"], function () {
                Route::resource('/', AppChannelFacebookPagesController::class)->names('app.channelfacebookpages');
                Route::get('oauth', [AppChannelFacebookPagesController::class, 'oauth'])->name('app.channelfacebookpages.oauth');
            });
        });
    });

    Route::group(["prefix" => "admin/api-integration"], function () {
        Route::get('facebook', [AppChannelFacebookPagesController::class, 'settings'])->name('app.channelfacebookpages.settings');
    });
});