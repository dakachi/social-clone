<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminMailSender\Http\Controllers\AdminMailSenderController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "mail-sender"], function () {
            Route::resource('', AdminMailSenderController::class)->only(['index'])->names('admin.mail-sender');
            Route::post('update', [AdminMailSenderController::class, 'update'])->name('admin.mail-sender.update');
            Route::post('list', [AdminMailSenderController::class, 'list'])->name('admin.mail-sender.list');
            Route::post('save', [AdminMailSenderController::class, 'save'])->name('admin.mail-sender.save');
            Route::post('status/{any}', [AdminMailSenderController::class, 'status'])->name('admin.mail-sender.status');
            Route::post('destroy', [AdminMailSenderController::class, 'destroy'])->name('admin.mail-sender.destroy');
        });

        Route::group(["prefix" => "settings"], function () {
            Route::get('mail-sender', [AdminMailSenderController::class, 'settings'])->name('admin.mail-sender.settings');
        });

    });
});
