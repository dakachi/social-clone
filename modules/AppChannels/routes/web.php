<?php

use Illuminate\Support\Facades\Route;
use Modules\AppChannels\Http\Controllers\AppChannelsController;

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
        Route::group(["prefix" => "channels"], function () {
            Route::resource('/', AppChannelsController::class)->names('app.channels');
            Route::get('add', [AppChannelsController::class, 'add'])->name('app.channels.add');
            Route::post('save', [AppChannelsController::class, 'save'])->name('app.channels.save');
            Route::post('list', [AppChannelsController::class, 'list'])->name('app.channels.list');
            Route::post('status/{any}', [AppChannelsController::class, 'status'])->name('app.channels.status');
            Route::post('destroy', [AppChannelsController::class, 'destroy'])->name('app.channels.destroy');
        });
    });
});