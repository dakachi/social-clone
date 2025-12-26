<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminMailServer\Http\Controllers\AdminMailServerController;

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
    Route::group(["prefix" => "admin/settings"], function () {
        Route::resource('mail-server', AdminMailServerController::class)->only(['index'])->names('admin.settings');
    });

    Route::post('admin/mail-server/test', [AdminMailServerController::class, 'testSendEmail'])->name('admin.mail-server.test');
});
