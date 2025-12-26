<?php

use Illuminate\Support\Facades\Route;
use Modules\AppPublishingLabels\Http\Controllers\AppPublishingLabelsController;

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
    Route::group(["prefix" => "publishing"], function () {
        Route::group(["prefix" => "labels"], function () {
            Route::resource('/', AppPublishingLabelsController::class)->names('app.publishingLabels');
            Route::post('list', [AppPublishingLabelsController::class, 'list'])->name('admin.publishingLabels.list');
            Route::get('create', [AppPublishingLabelsController::class, 'create'])->name('admin.publishingLabels.create');
            Route::get('edit/{any}', [AppPublishingLabelsController::class, 'edit'])->name('admin.publishingLabels.edit');
            Route::post('save', [AppPublishingLabelsController::class, 'save'])->name('admin.publishingLabels.save');
            Route::post('destroy', [AppPublishingLabelsController::class, 'destroy'])->name('admin.publishingLabels.destroy');
            Route::post('status/{any}', [AppPublishingLabelsController::class, 'status'])->name('admin.publishingLabels.status');
        });
    });
});