<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAICategories\Http\Controllers\AdminAICategoriesController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "ai/categories"], function () {
            Route::resource('/', AdminAICategoriesController::class)->names('admin.ai.categories');
            Route::post('update', [AdminAICategoriesController::class, 'update'])->name('admin.ai.categories.update');
            Route::post('save', [AdminAICategoriesController::class, 'save'])->name('admin.ai.categories.save');
            Route::post('list', [AdminAICategoriesController::class, 'list'])->name('admin.ai.categories.list');
            Route::post('status/{any}', [AdminAICategoriesController::class, 'status'])->name('app.ai.categories.status');
            Route::post('destroy', [AdminAICategoriesController::class, 'destroy'])->name('admin.ai.categories.destroy');

        });
    });
});