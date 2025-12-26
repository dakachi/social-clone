<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\AppRssSchedules\Http\Controllers\AppRssSchedulesController;

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
        Route::group(["prefix" => "rss-schedules"], function () {
            Route::resource('/', AppRssSchedulesController::class)->only(['index'])->names('app.rss-schedules');
            Route::post('save', [AppRssSchedulesController::class, 'save'])->name('app.rss-schedules.save');
            Route::post('list', [AppRssSchedulesController::class, 'list'])->name('app.rss-schedules.list');
            Route::post('status/{any}', [AppRssSchedulesController::class, 'status'])->name('app.rss-schedules.status');
            Route::post('destroy', [AppRssSchedulesController::class, 'destroy'])->name('app.rss-schedules.destroy');
            Route::get('create', [AppRssSchedulesController::class, 'create'])->name('app.rss-schedules.create');
            Route::get('edit/{id}', [AppRssSchedulesController::class, 'edit'])->name('app.rss-schedules.edit');
        });
    });
});

Route::get("app/rss-schedules/cron", function (Request $request) {
    $key = $request->input('key');
    $cron_key = get_option("cron_key", rand_string());
    if ($key !== $cron_key) abort(403);
    app(\Modules\AppRssSchedules\Console\RssCronJobCommand::class)->handle();
    return "Cronjob executed.";
});