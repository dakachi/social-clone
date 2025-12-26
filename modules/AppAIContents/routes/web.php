<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAIContents\Http\Controllers\AppAIContentsController;

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
        Route::group(["prefix" => "ai-contents"], function () {
            Route::resource('/', AppAIContentsController::class)->names('app.ai-contents');
            Route::post('categories', [AppAIContentsController::class, 'categories'])->name('app.ai-contents.categories');
            Route::post('templates', [AppAIContentsController::class, 'templates'])->name('app.ai-contents.templates');
            Route::post('process', [AppAIContentsController::class, 'process'])->name('app.ai-contents.process');
            Route::post('process/{any}', [AppAIContentsController::class, 'process'])->name('app.ai-contents.process');
            Route::post('create-content', [AppAIContentsController::class, 'createContent'])->name('app.ai-contents.create_content');
            Route::post('popup-ai-content', [AppAIContentsController::class, 'popupAIContent'])->name('app.ai-contents.popupAIContent');

        });
    });
});