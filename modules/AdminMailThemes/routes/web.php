<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminMailThemes\Http\Controllers\AdminMailThemesController;

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
    Route::group(["prefix" => "admin/mail"], function () {
        Route::group(["prefix" => "themes"], function () {
            Route::resource('/', AdminMailThemesController::class)->only(['index'])->names('admin.mail.themes');
            Route::get('import/{id}', [AdminMailThemesController::class, 'import'])->name('admin.mail.themes.import');
            Route::post('destroy', [AdminMailThemesController::class, 'destroy'])->name('admin.mail.themes.destroy');
            Route::post('set-default', [AdminMailThemesController::class, 'setDefault'])->name('admin.mail.themes.set_default');
        });

    });
});
