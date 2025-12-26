<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAIPrompts\Http\Controllers\AppAIPromptsController;

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
        Route::group(["prefix" => "ai-prompts"], function () {
            Route::resource('/', AppAIPromptsController::class)->only(['index'])->names('app.ai-prompts');
            Route::post('list', [AppAIPromptsController::class, 'list'])->name('app.ai-prompts.list');
            Route::post('update', [AppAIPromptsController::class, 'update'])->name('app.ai-prompts.update');
            Route::post('save', [AppAIPromptsController::class, 'save'])->name('app.ai-prompts.save');
            Route::post('destroy', [AppAIPromptsController::class, 'destroy'])->name('app.ai-prompts.destroy');
        });
    });
});