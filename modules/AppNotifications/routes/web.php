<?php

use Illuminate\Support\Facades\Route;
use Modules\AppNotifications\Http\Controllers\AppNotificationsController;

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

Route::group(["prefix" => "app"], function () {
    Route::prefix('notifications')->name('app.notifications.')->middleware(['auth'])->group(function () {
        Route::get('/', [AppNotificationsController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [AppNotificationsController::class, 'markAllRead'])->name('markAllRead');
        Route::post('/mark-read/{id}', [AppNotificationsController::class, 'markAsRead'])->name('markAsRead');
        
        Route::post('/archive-all', [AppNotificationsController::class, 'archiveAll'])->name('archiveAll');
    });

});