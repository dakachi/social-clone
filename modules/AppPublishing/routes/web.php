<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\AppPublishing\Http\Controllers\AppPublishingController;

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
        Route::group(["prefix" => "publishing"], function () {
            Route::resource('/', AppPublishingController::class)->only(['index'])->names('app.publishing');
            Route::get('events', [AppPublishingController::class, 'events'])->name('app.publishing.events');
            Route::post('composer', [AppPublishingController::class, 'composer'])->name('app.publishing.composer');
            Route::post('preview', [AppPublishingController::class, 'preview'])->name('app.publishing.preview');
            Route::post('getLinkInfo', [AppPublishingController::class, 'getLinkInfo'])->name('app.publishing.getLinkInfo');
            Route::post('destroy', [AppPublishingController::class, 'destroy'])->name('app.publishing.destroy');
            Route::post('destroy-by-filters', [AppPublishingController::class, 'destroyByFilter'])->name('app.publishing.destroy_by_filter');
            Route::post('save', [AppPublishingController::class, 'save'])->name('app.publishing.save');
            Route::post('changePostDate', [AppPublishingController::class, 'changePostDate'])->name('app.publishing.changePostDate');

        });
    });
});

Route::get("app/publishing/cron", function (Request $request) {
    $key = $request->input('key');
    $cron_key = get_option("cron_key", rand_string());
    if ($key !== $cron_key) abort(403);
    app(\Modules\AppPublishing\Console\CronJobCommand::class)->handle();
    return "Cronjob executed.";
});