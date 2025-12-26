<?php

use Illuminate\Support\Facades\Route;
use Modules\AppMediaSearch\Http\Controllers\AppMediaSearchController;

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
        Route::group(["prefix" => "search-media"], function () {
            Route::resource('/', AppMediaSearchController::class)->names('app.search_media');
            Route::post('popup_search', [AppMediaSearchController::class, 'popup_search'])->name('app.search_media.popup_search');
            Route::post('search', [AppMediaSearchController::class, 'search'])->name('app.search_media.search');
        });
    });

    Route::group(["prefix" => "admin/settings"], function () {
        Route::get('search-media', [AppMediaSearchController::class, 'settings'])->name('app.search_media.settings');
    });
});
