<?php

use Illuminate\Support\Facades\Route;
use Modules\AppURLShorteners\Http\Controllers\AppURLShortenersController;

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
        Route::group(["prefix" => "url-shorteners"], function () {
            Route::post('shorten', [AppURLShortenersController::class, 'shorten'])->name('app.url-shorteners.shorten');
        });
    });
});