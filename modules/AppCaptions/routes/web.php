<?php

use Illuminate\Support\Facades\Route;
use Modules\AppCaptions\Http\Controllers\AppCaptionsController;

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
    Route::group(["prefix" => "captions"], function () {
        Route::resource('/', AppCaptionsController::class)->names('app.captions');
        Route::post('update', [AppCaptionsController::class, 'update'])->name('app.captions.update');
        Route::post('save', [AppCaptionsController::class, 'save'])->name('app.captions.save');
        Route::post('list', [AppCaptionsController::class, 'list'])->name('app.captions.list');
        Route::post('list/popup', [AppCaptionsController::class, 'list'])->name('app.captions.popup_list');
        Route::post('save_cation', [AppCaptionsController::class, 'saveCation'])->name('app.captions.save_cation');
        Route::post('get_cation', [AppCaptionsController::class, 'getCation'])->name('app.captions.get_cation');
        Route::post('destroy', [AppCaptionsController::class, 'destroy'])->name('app.captions.destroy');
    });
});