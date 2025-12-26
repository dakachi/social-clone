<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminSupportTypes\Http\Controllers\AdminSupportTypesController;

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
    Route::group(["prefix" => "admin/support/types"], function () {
        Route::resource('/', AdminSupportTypesController::class)->only(['index'])->names('admin.types');
        Route::post('list', [AdminSupportTypesController::class, 'list'])->name('admin.types.list');
        Route::post('update', [AdminSupportTypesController::class, 'update'])->name('admin.types.update');
        Route::post('save', [AdminSupportTypesController::class, 'save'])->name('admin.types.save');
        Route::post('status/{any}', [AdminSupportTypesController::class, 'status'])->name('admin.types.status');
        Route::post('destroy', [AdminSupportTypesController::class, 'destroy'])->name('admin.types.destroy');
    });
});
