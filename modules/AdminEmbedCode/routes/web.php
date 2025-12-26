<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminEmbedCode\Http\Controllers\AdminEmbedCodeController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "embed-code"], function () {
            Route::get('/', [AdminEmbedCodeController::class, 'settings'])->name('admin.embed-code');
        });
    });
});
