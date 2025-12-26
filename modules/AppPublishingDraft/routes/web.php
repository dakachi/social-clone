<?php

use Illuminate\Support\Facades\Route;
use Modules\AppPublishingDraft\Http\Controllers\AppPublishingDraftController;

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
        Route::group(["prefix" => "draft"], function () {
            Route::resource('/', AppPublishingDraftController::class)->names('app.publishing.draft');
            Route::post('list', [AppPublishingDraftController::class, 'list'])->name('app.publishing.draft.list');
            Route::get('create', [AppPublishingDraftController::class, 'create'])->name('app.publishing.draft.create');
            Route::get('edit/{any}', [AppPublishingDraftController::class, 'edit'])->name('app.publishing.draft.edit');
            Route::post('save', [AppPublishingDraftController::class, 'save'])->name('app.publishing.draft.save');
            Route::post('destroy', [AppPublishingDraftController::class, 'destroy'])->name('app.publishing.draft.destroy');
            Route::post('status/{any}', [AppPublishingDraftController::class, 'status'])->name('app.publishing.draft.status');
        });
    });
});