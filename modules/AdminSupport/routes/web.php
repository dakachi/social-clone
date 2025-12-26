<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSupport\Http\Controllers\AdminSupportController;

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
    Route::group(["prefix" => "admin/support"], function () {
        Route::resource('/', AdminSupportController::class)->only(['index'])->names('admin.support');
        Route::get('ticket/{any}', [AdminSupportController::class, 'ticket'])->name('admin.support.ticket');
        Route::post('list', [ AdminSupportController::class, 'list' ])->name('admin.support.list');
        Route::post('update', [AdminSupportController::class, 'update'])->name('admin.support.update');
        Route::post('save', [AdminSupportController::class, 'save'])->name('admin.support.save');
        Route::post('destroy', [AdminSupportController::class, 'destroy'])->name('admin.support.destroy');
        Route::post('status/{any}', [AdminSupportController::class, 'status'])->name('admin.support.status');

        Route::get('new-ticket', [AdminSupportController::class, 'update_ticket'])->name('admin.support.new-ticket');
        Route::get('edit/{id}', [AdminSupportController::class, 'update_ticket'])->name('admin.support.edit');

        Route::post('edit_comment', [AdminSupportController::class, 'edit_comment'])->name('admin.support.edit_comment');
        Route::post('save_comment', [AdminSupportController::class, 'save_comment'])->name('admin.support.save_comment');
        Route::post('load-comment', [ AdminSupportController::class, 'load_comment' ])->name('admin.support.comment');
        Route::post('delete_comment', [AdminSupportController::class, 'delete_comment'])->name('admin.support.delete_comment');
    });
}); 