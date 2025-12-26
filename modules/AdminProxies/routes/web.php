<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminProxies\Http\Controllers\AdminProxiesController;

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
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "proxies"], function () {
            Route::resource('', AdminProxiesController::class)->names('admin.proxies');
            Route::post('list', [AdminProxiesController::class, 'list'])->name('admin.proxies.list');
            Route::post('update', [AdminProxiesController::class, 'update'])->name('admin.proxies.update');
            Route::post('save', [AdminProxiesController::class, 'save'])->name('admin.proxies.save');
            Route::post('destroy', [AdminProxiesController::class, 'destroy'])->name('admin.proxies.destroy');
            Route::post('status/{any}', [AdminProxiesController::class, 'status'])->name('admin.proxies.status');
        });
    });
});