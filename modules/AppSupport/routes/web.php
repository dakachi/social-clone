<?php

use Illuminate\Support\Facades\Route;
use Modules\AppSupport\Http\Controllers\AppSupportController;


Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "app/support"], function () {
        Route::resource('/', AppSupportController::class)->only(['index'])->names('app.support');
        Route::get('new-ticket', [AppSupportController::class, 'new_ticket'])->name('app.support.new-ticket');
        Route::post('list', [ AppSupportController::class, 'list' ])->name('app.support.list');
        Route::post('save', [AppSupportController::class, 'save'])->name('app.support.save');
        Route::post('resolved', [AppSupportController::class, 'resolved'])->name('app.support.resolved');
        Route::get('ticket/{any}', [AppSupportController::class, 'ticket'])->name('app.support.ticket');
        Route::post('save_comment', [AppSupportController::class, 'save_comment'])->name('app.support.save_comment');
        Route::post('load-comment', [ AppSupportController::class, 'load_comment' ])->name('app.support.comment');
    });
});
