<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminFrontendThemes\Http\Controllers\AdminFrontendThemesController;

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
    Route::group(["prefix" => "admin/themes"], function () {
        Route::group(["prefix" => "frontend"], function () {
            Route::resource('/', AdminFrontendThemesController::class)->only(['index'])->names('admin.themes.frontend');
            Route::post('import', [AdminFrontendThemesController::class, 'import'])->name('admin.themes.frontend.import');
            Route::post('destroy', [AdminFrontendThemesController::class, 'destroy'])->name('admin.themes.frontend.destroy');
            Route::post('set-default', [AdminFrontendThemesController::class, 'setDefault'])->name('admin.themes.frontend.set_default');
        });

    });
});
