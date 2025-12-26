<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminNotifications\Http\Controllers\AdminNotificationsController;

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
    Route::group(["prefix" => "admin"], function () {
        Route::group(["prefix" => "notifications"], function () {
            Route::resource('', AdminNotificationsController::class)->only(['index'])->names('admin.notifications');
            Route::post('update', [AdminNotificationsController::class, 'update'])->name('admin.notifications.update');
            Route::post('list', [AdminNotificationsController::class, 'list'])->name('admin.notifications.list');
            Route::post('save', [AdminNotificationsController::class, 'save'])->name('admin.notifications.save');
            Route::post('status/{any}', [AdminNotificationsController::class, 'status'])->name('admin.notifications.status');
            Route::post('destroy', [AdminNotificationsController::class, 'destroy'])->name('admin.notifications.destroy');
        });
    });
});
