<?php

use Illuminate\Support\Facades\Route;
use Modules\AppPublishingCampaigns\Http\Controllers\AppPublishingCampaignsController;

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
        Route::group(["prefix" => "campaigns"], function () {
            Route::resource('/', AppPublishingCampaignsController::class)->names('app.publishingcampaigns');
            Route::post('list', [AppPublishingCampaignsController::class, 'list'])->name('app.publishingcampaigns.list');
            Route::get('create', [AppPublishingCampaignsController::class, 'create'])->name('app.publishingcampaigns.create');
            Route::get('edit/{any}', [AppPublishingCampaignsController::class, 'edit'])->name('app.publishingcampaigns.edit');
            Route::post('save', [AppPublishingCampaignsController::class, 'save'])->name('app.publishingcampaigns.save');
            Route::post('destroy', [AppPublishingCampaignsController::class, 'destroy'])->name('app.publishingcampaigns.destroy');
            Route::post('status/{any}', [AppPublishingCampaignsController::class, 'status'])->name('app.publishingcampaigns.status');
        });
    });
});