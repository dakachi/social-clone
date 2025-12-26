<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminAITemplates\Http\Controllers\AdminAITemplatesController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "ai/templates"], function () {
            Route::resource('', AdminAITemplatesController::class)->names('admin.ai.templates');
            Route::post('update', [AdminAITemplatesController::class, 'update'])->name('admin.ai.templates.update');
            Route::post('list', [AdminAITemplatesController::class, 'list'])->name('admin.ai.templates.list');
            Route::post('save', [AdminAITemplatesController::class, 'save'])->name('admin.ai.templates.save');
            Route::post('status/{any}', [AdminAITemplatesController::class, 'status'])->name('admin.ai.templates.status');
            Route::post('destroy', [AdminAITemplatesController::class, 'destroy'])->name('admin.ai.templates.destroy');
        });

    });
});
