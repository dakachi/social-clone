<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSupportLabels\Http\Controllers\AdminSupportLabelsController;

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
        Route::group(["prefix" => "support/labels"], function () {
            Route::resource('/', AdminSupportLabelsController::class)->names('admin.support.labels');
            Route::post('update', [AdminSupportLabelsController::class, 'update'])->name('admin.support.labels.update');
            Route::post('save', [AdminSupportLabelsController::class, 'save'])->name('admin.support.labels.save');
            Route::post('status/{any}', [AdminSupportLabelsController::class, 'status'])->name('admin.labels.status');            
            Route::post('list', [AdminSupportLabelsController::class, 'list'])->name('admin.support.labels.list');
            Route::post('destroy', [AdminSupportLabelsController::class, 'destroy'])->name('admin.support.labels.destroy');
        });
    });
});
