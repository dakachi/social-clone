<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\AppAIPublishing\Http\Controllers\AppAIPublishingController;

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
        Route::group(["prefix" => "ai-publishing"], function () {
            Route::resource('/', AppAIPublishingController::class)->only(['index'])->names('app.ai-publishing');
            Route::get('create', [AppAIPublishingController::class, 'update'])->name('app.ai-publishing.create');
            Route::post('list', [AppAIPublishingController::class, 'list'])->name('app.ai-publishing.list');
            Route::get('edit/{id}', [AppAIPublishingController::class, 'update'])->name('app.ai-publishing.edit');
            Route::post('save', [AppAIPublishingController::class, 'save'])->name('app.ai-publishing.save');
            Route::post('destroy', [AppAIPublishingController::class, 'destroy'])->name('app.ai-publishing.destroy');
            Route::post('status/{any}', [AppAIPublishingController::class, 'status'])->name('app.ai-publishing.status');
        });
    });
});

Route::get("app/ai-publishing/cron", function (Request $request) {
    $key = $request->input('key');
    $cron_key = get_option("cron_key", rand_string());
    if ($key !== $cron_key) abort(403);
    app(\Modules\AppAIPublishing\Console\CronJobCommand::class)->handle();
    return "Cronjob executed.";
});