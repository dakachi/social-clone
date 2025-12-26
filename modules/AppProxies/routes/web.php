<?php

use Illuminate\Support\Facades\Route;
use Modules\AppProxies\Http\Controllers\AppProxiesController;

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
        Route::group(["prefix" => "proxies"], function () {
            Route::resource('', AppProxiesController::class)->names('app.proxies');
            Route::post('list', [AppProxiesController::class, 'list'])->name('app.proxies.list');
            Route::post('update', [AppProxiesController::class, 'update'])->name('app.proxies.update');
            Route::post('save', [AppProxiesController::class, 'save'])->name('app.proxies.save');
            Route::post('destroy', [AppProxiesController::class, 'destroy'])->name('app.proxies.destroy');
            Route::post('status/{any}', [AppProxiesController::class, 'status'])->name('app.proxies.status');
        });
    });
});